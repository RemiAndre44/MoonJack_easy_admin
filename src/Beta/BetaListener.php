<?php
/**
 * Created by PhpStorm.
 * User: remi.andre
 * Date: 06/03/2019
 * Time: 10:41
 */

namespace App\Beta;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class BetaListener
{

    protected $betaHTML;
    protected $endDate;

    public function __construct(BetaHTMLAdder $betaHTML, $endDate)
    {
        $this->betaHTML = $betaHTML;
        $this->endDate = new \DateTime($endDate);
    }

    public function processBeta(FilterResponseEvent $event){
        if(!$event->isMasterRequest()){
            return;
        }

        $remainingDays = $this->endDate->diff(new \DateTime())->days;

        if($remainingDays <= 0){
            return;
        }

        $response = $this->betaHTML->addBeta($event->getResponse(), $remainingDays) ;

        $event->setResponse($response);
    }

}