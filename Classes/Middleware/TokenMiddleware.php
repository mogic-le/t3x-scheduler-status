<?php


namespace Mogic\Typo3SchedulerMonitoring\Middleware;

class TokenMiddleware{

    public function checkRequestSecurityToken($configurationToken){

        if(isset($_GET['token'])){
            if(trim($_GET['token'])!='' && trim($_GET['token'])===$configurationToken){
                return true;
            }
        }
        return false;
    }
}


