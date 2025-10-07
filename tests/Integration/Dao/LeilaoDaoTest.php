<?php

namespace Alura\Leilao\Tests\Integration\Dao;

use Alura\Leilao\Dao\Leilao as DaoLeilao;
use Alura\Leilao\Infra\ConnectionCreator;
use Alura\Leilao\Model\Leilao;
use PHPUnit\Framework\TestCase;

class LeilaoDaoTest extends TestCase
{
    
    private static $pdo;

    public static function setUpBeforeClass(): void
    {
        /* self::$pdo = new \PDO('sqlite::memory:');
        self::$pdo->exec('drop table if exists leiloes');
        self::$pdo->exec('create table leiloes (
            id INTEGER PRIMARY KEY,
            descricao TEXT,
            dataInicio TEXT,
            finalizado BOOL
        )'); */
        self::$pdo = ConnectionCreator::getConnection();

    }

    protected function setUp(): void
    {
        self::$pdo->beginTransaction();        
        self::$pdo->exec('DELETE FROM leiloes where true');
        
    }

    /**
     * @dataProvider leiloes
     */
    public function testInsercaoEBuscaDevemFuncionar(array $leiloes)
    {
        
        $leilaoDao = new DaoLeilao(self::$pdo);

        foreach($leiloes as $leilao){
            
            $leilaoDao->salva($leilao);
             
        }

        $leiloes = $leilaoDao->recuperarNaoFinalizados();

        $this->assertCount(1, $leiloes);
        $this->assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        $this->assertSame('Variante 0km', $leiloes[0]->recuperarDescricao());
                
    } 

    /**
     * @dataProvider leiloes
     */
    public function testBuscaLeiloesNaoFinalizados(array $leiloes)
    {
        
        $leilaoDao = new DaoLeilao(self::$pdo);

        foreach($leiloes as $leilao){
            
            $leilaoDao->salva($leilao);
             
        }
        
        $leiloes = $leilaoDao->recuperarNaoFinalizados();

        $this->assertCount(1, $leiloes);
        $this->assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        $this->assertSame('Variante 0km', $leiloes[0]->recuperarDescricao());
        $this->assertFalse($leiloes[0]->estaFinalizado());
                
    } 

    /**
     * @dataProvider leiloes
     */
    public function testBuscaLeiloesFinalizados(array $leiloes)
    {
        
        $leilaoDao = new DaoLeilao(self::$pdo);

        foreach($leiloes as $leilao){
            
            $leilaoDao->salva($leilao);
             
        }
        
        $leiloes = $leilaoDao->recuperarFinalizados();
        $this->assertCount(1, $leiloes);
        $this->assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        $this->assertSame('Fiat 147 0km', $leiloes[0]->recuperarDescricao());
        $this->assertTrue($leiloes[0]->estaFinalizado());
                
    } 

    /**
     * @dataProvider leiloes
     */
    public function testAoAtualizarLeilaoStatusDeveSerAlterado(array $leiloes)
    {
        
        $leilao = new Leilao('Brasilia Amarela');
        $leilaoDao = new DaoLeilao(self::$pdo);
        $leilao = $leilaoDao->salva($leilao);
        $leilao->finaliza();

        $leiloes = $leilaoDao->recuperarNaoFinalizados();
        $this->assertCount(1, $leiloes);
        $this->assertSame('Brasilia Amarela', $leiloes[0]->recuperarDescricao());
        $this->assertFalse($leiloes[0]->estaFinalizado());

        $leilaoDao->atualiza($leilao);

        $leiloes = $leilaoDao->recuperarFinalizados();
        $this->assertCount(1, $leiloes);
        $this->assertSame('Brasilia Amarela', $leiloes[0]->recuperarDescricao());
        $this->assertTrue($leiloes[0]->estaFinalizado());
                
    } 

    protected function tearDown(): void
    {
        //self::$pdo->exec('DELETE FROM leiloes');
        self::$pdo->rollBack();
    }

    public function leiloes(){
        
        $naoFinalizado = new Leilao('Variante 0km');
        $finalizado = new Leilao('Fiat 147 0km');
        $finalizado->finaliza();
        
        return [
            [
                [$naoFinalizado, $finalizado]
            ]
        ];
    }
}