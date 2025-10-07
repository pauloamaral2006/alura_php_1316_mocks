<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorEmail
{

    public function notificarTerminoLeilao(Leilao $leilao): void
    {
        
        $suceso = mail(
            'usuario@email.com',
            'Leilão finalizado',
            ''
        );

        if(!$suceso){
            throw new \DomainException('Erro ao enviar o e-mail.');
        }
    }

}