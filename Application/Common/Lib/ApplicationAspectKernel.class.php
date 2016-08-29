<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/29
 * Time: 10:10
 */
require_once('Application/Common/Aspect/MonitorAspect.class.php');
use Go\Core\AspectKernel;
use Go\Core\AspectContainer;
use Aspect\MonitorAspect;

/**
 * Application Aspect Kernel
 */
class ApplicationAspectKernel extends AspectKernel
{
    protected function configureAop(AspectContainer $container){
        $container->registerAspect(new MonitorAspect());
    }
}