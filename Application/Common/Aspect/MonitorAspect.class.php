<?php
namespace Aspect;
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/29
 * Time: 10:15
 */
use Go\Aop\Aspect;
use Go\Aop\Intercept\FieldAccess;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Pointcut;


class MonitorAspect implements Aspect
{
    /**
     * @param MethodInvocation $invocation Invocation
     * @Before("execution(public App\Controller\DemoController->*(*))")
     */
    public function beforeMethodExecution(MethodInvocation $invocation){
        var_dump('1');
        $obj = $invocation->getThis();
        echo 'Calling Before Interceptor for method: ',
        is_object($obj) ? get_class($obj) : $obj,
        $invocation->getMethod()->isStatic() ? '::' : '->',
        $invocation->getMethod()->getName(),
        '()',
        ' with arguments: ',
        json_encode($invocation->getArguments()),
        "<br>\n";
    }
}