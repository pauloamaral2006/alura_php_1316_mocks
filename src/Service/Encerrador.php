<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;

class Encerrador
{
    private $dao;
    private $enviadorEmail;

    public function __construct(LeilaoDao $leilaoDao, EnviadorEmail $enviadorEmail)
    {
        $this->dao = $leilaoDao;
        $this->enviadorEmail = $enviadorEmail;
    }
    
    public function encerra()
    {

        $leiloes = $this->dao->recuperarNaoFinalizados();

        foreach ($leiloes as $leilao) {
            if ($leilao->temMaisDeUmaSemana()) {

                try {
                    $leilao->finaliza();
                    $this->dao->atualiza($leilao);
                    $this->enviadorEmail->notificarTerminoLeilao($leilao);
                } catch (\DomainException $th) {
                    error_log($th->getMessage());
                }
                
            }
        }
    }
}
