<?php

namespace Alura\Leilao\Tests\Domain;

use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Service\EnviadorEmail;
use DomainException;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;

class LeilaoDaoMock extends LeilaoDao
{

    private $leiloes = [];

    public function salva(Leilao $leilao): void
    {    
        $this->leiloes[] = $leilao;
    }
    public function atualiza(Leilao $leilao)
    {  
    }
    
    public function recuperarNaoFinalizados(): array
    {    
        return array_filter($this->leiloes, function(Leilao $leilao) {
            return !$leilao->estaFinalizado();
        });
    }
    
    public function recuperarFinalizados(): array
    {    
        return array_filter($this->leiloes, function(Leilao $leilao) {
            return $leilao->estaFinalizado();
        });
    }

}

class EncerradorTest extends TestCase
{

    private $encerrador;
    private $fiat147;
    private $variante;
    private $enviadorEmail;

    protected function setUp(): void
    {
       
        $this->fiat147 = new Leilao('Fiat 147 0km', new \DateTimeImmutable('8 days ago'));
        $this->variante = new Leilao('Variant 1972 0km', new \DateTimeImmutable('10 days ago'));

        $leilaoDao = $this->createMock(LeilaoDaoMock::class);
        /* $leilaoDao = $this->getMockBuilder(LeilaoDaoMock::class)
            ->disableOriginalConstructor()
            ->setConstructorArgs([new PDO('sqlite::memory')])
            ->getMock(); */
        $leilaoDao->method('recuperarNaoFinalizados')
            ->willReturn([$this->fiat147, $this->variante]);
        $leilaoDao->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$this->fiat147],
                [$this->variante],
            );
        $this->enviadorEmail = $this->createMock(EnviadorEmail::class);
        $this->encerrador = new Encerrador($leilaoDao, $this->enviadorEmail);

    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {

        
        $this->encerrador->encerra();

        $leiloes = [$this->fiat147, $this->variante];
        $this->assertEquals('Fiat 147 0km', $leiloes[0]->recuperarDescricao());
        $this->assertEquals('Variant 1972 0km', $leiloes[1]->recuperarDescricao());
    }

    public function testDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmail(): void
    {
        
        $e = new DomainException('Erro ao enviar e-mail');
        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willThrowException($e);
        $this->encerrador->encerra();
    }

    public function testSoDeveEnviarLeilaoPorEmailAposFinalizado()
    {
        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            //->with($this->lessThanOrEqual(1))
            ->willReturnCallback(function(Leilao $leilao) {
                $this->assertTrue($leilao->estaFinalizado());
            });
        $this->encerrador->encerra();
    }

}