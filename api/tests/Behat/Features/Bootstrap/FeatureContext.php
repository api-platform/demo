<?php

namespace App\Tests\Behat\Features\Bootstrap;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\RestContext;
use Behatch\HttpCall\Request;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Symfony\Component\HttpFoundation\Response;


class FeatureContext extends RestContext
{
    /**
     * @var JsonInspector
     */
    private $inspector;

    /**
     * @var \Behatch\Context\RestContext
     */
    private $restContext;

    public function __construct(Request $request)
    {
        //print_r($request);die;
        parent::__construct($request);
        $this->inspector = new JsonInspector('javascript');
    }

    /**
     * @Then the JSON node :node should be greater than the number :number
     */
    public function theJsonNodeShouldBeGreaterThanTheNumber($node, $number)
    {
        $value = $this->inspector->evaluate(new Json($this->request->getContent()), $node);
        $this->assertTrue($value > $number);
    }

    /**
     * @Then dump the response
     */
    public function dumpTheResponse()
    {
        $response = $this->request->getContent();
        var_dump($response);
    }

    /**
     * @Then the JSON node :node length should be :length
     */
    public function theJsonNodeLengthShouldBeEqualsTo($node, $length)
    {
        $value = $this->inspector->evaluate(new Json($this->request->getContent()), $node);
        $this->assertEquals($length, strlen($value));
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        try {
            $this->restContext = $environment->getContext('Behatch\Context\RestContext');
        } catch (\Exception $e) {
            // no such context, probably not registered in behat.yml
        }
    }

    /**
     * @When /^I send a "([^"]*)" project request to "([^"]*)" where id is "([^"]*)" from last request with body:$/
     */
    public function iSendARequestWithIdFormLastRequestWithBody($method, $url, $node, PyStringNode $body)
    {
        return $this->iSendARequestWithIdFormLastRequest($method, $url, $node, $body);
    }

    /**
     * @When /^I send a "([^"]*)" project request to "([^"]*)" where id is "([^"]*)" from last request$/
     */
    public function iSendARequestWithIdFormLastRequest($method, $url, $node, $body = null, $files = [])
    {
        $json = new Json($this->request->getContent());
        $id = $this->inspector->evaluate($json, $node);

        $urlWithId = str_replace('$id', $id, $url);
        return $this->request->send(
            $method,
            $this->locatePath($urlWithId),
            [],
            $files,
            $body !== null ? $body->getRaw() : null
        );
    }

    /**
     * Checks, that current page response status is equal to specified
     * Example: Then the response status code should be 200
     * Example: And the response status code should be 400
     *
     * @Then the response status HTTP should be :code
     */
    public function theResponseStatusHttpShouldBe($code)
    {
        $this->assertSession()->statusCodeEquals($this->getCode($code));
    }

    public function getCode($code)
    {
        return constant(Response::class . '::' . $code);
    }
}
