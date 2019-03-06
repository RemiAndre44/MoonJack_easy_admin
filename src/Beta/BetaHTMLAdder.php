<?php
/**
 * Created by PhpStorm.
 * User: remi.andre
 * Date: 06/03/2019
 * Time: 10:35
 */

namespace App\Beta;


use Symfony\Component\HttpFoundation\Response;

class BetaHTMLAdder
{

    public function addBeta(Response $response, $remainingDays){
        $content = $response->getContent();

        $html = '<div style="position: absolute; top: 0; z-index: 1; background: orange; width: 100%; text-align: center; padding: 0.5em;">Beta J-'.(int) $remainingDays.'</div>';

        $content = str_replace(
            '<body>',
            '<body> '.$html,
            $content
        );

        $response->setContent($content);

        return $response;
    }
}