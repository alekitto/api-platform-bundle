<?php declare(strict_types=1);

namespace Kcs\ApiPlatformBundle\Tests\Fixtures\Decoder\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;

class TestController extends Controller
{
    use VarDumperTestTrait;

    public function indexAction(Request $request)
    {
        return new Response($this->getDump($request->request->all()));
    }
}
