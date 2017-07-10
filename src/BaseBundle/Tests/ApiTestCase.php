<?php

namespace BaseBundle\Tests;

use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DomCrawler\Crawler;

class ApiTestCase extends KernelTestCase
{

    /**
     * @var array
     */
    private static $history = array();

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @var FormatterHelper
     */
    private $formatterHelper;

    /**
     * @var bool
     */
    protected $useJWTAuth = true;

    private $responseAsserter;


    protected static $handler = null;

    /**
     * Creates a Client.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return Client A Client instance
     */
    protected static function createHandler()
    {
        if (is_null(static::$handler)) {
            $curl = new CurlMultiHandler();

            static::$handler = HandlerStack::create($curl);
            static::$handler->push(Middleware::history(self::$history));


            static::$handler->push(Middleware::mapRequest(function (RequestInterface $request) {
                $path = $request->getUri()->getPath();
                if (strpos($path, '/app_test.php') !== 0) {
                    $path = '/app_test.php' . $path;
                }

                $uri = $request->getUri()->withPath($path);

                return $request->withUri($uri);
            }));
        }
        return static::$handler;
    }

    /**
     * @beforeClass
     */
    public static function beforeClass()
    {
        self::bootKernel();
        self::createDatabaseSchema();
    }

    /**
     * Creates a mock object of a service identified by its id.
     *
     * @param string $id
     *
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected function getServiceMockBuilder($id)
    {
        $service = $this->getService($id);
        $class = get_class($service);
        return $this->getMockBuilder($class)->disableOriginalConstructor();
    }

    protected function createAuthenticatedClient()
    {
        return  $this->getAuthorizedHeaders("diego", ['Content-Type' => 'application/json']);
    }


    protected function tearUp()
    {
        $baseUrl = getenv('TEST_BASE_URL');

        if (!$baseUrl) {
            throw new AssertionFailedError('No TEST_BASE_URL environmental variable set in phpunit.xml.');
        }

        $clientData = [
            'base_uri' => $baseUrl,
            'http_errors' => false,
            'handler' => self::createHandler(),

        ];

        if ($this->useJWTAuth) {
            $clientData['headers'] =   $this->createAuthenticatedClient() ;
        }

        $this->client = new Client($clientData);

        // reset the history
        self::$history = array();
    }


    public static function createDatabaseSchema()
    {
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        // Drop and recreate tables for all entities
        try {
            $schemaTool->dropDatabase();
            $schemaTool->createSchema($metadata);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    protected function tearDown()
    {
        $this->purgeDatabase();
        $refl = new \ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }

        //parent::tearDown();
    }



    public function purgeDatabase()
    {
        $doctrine = self::$kernel->getContainer()->get('doctrine');
        $purger = new ORMPurger($doctrine->getManager());
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();
        //$doctrine->getConnection()->close();
    }

    protected function getService($id)
    {
        return self::$kernel->getContainer()
            ->get($id);
    }

    /**
     * Print last request URL
     */
    protected function printLastRequestUrl()
    {
        $lastRequest = $this->getLastRequest();

        if ($lastRequest) {
            $this->printDebug(sprintf('<comment>%s</comment>: <info>%s</info>', $lastRequest->getMethod(), $lastRequest->getUri()));
        } else {
            $this->printDebug('No request was made.');
        }
    }

    /**
     * Debug request detail
     * @param ResponseInterface $response
     */
    protected function debugResponse(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $name => $values) {
            $this->printDebug(sprintf('%s: %s', $name, implode(', ', $values)));
        }
        $body = (string) $response->getBody();

        $contentType = $response->getHeader('Content-Type');
        $contentType = $contentType[0];
        if ($contentType == 'application/json' || strpos($contentType, '+json') !== false) {
            $data = json_decode($body);
            if ($data === null) {
                // invalid JSON!
                $this->printDebug($body);
            } else {
                // valid JSON, print it pretty
                $this->printDebug(json_encode($data, JSON_PRETTY_PRINT));
            }
        } else {
            // the response is HTML - see if we should print all of it or some of it
            $isValidHtml = strpos($body, '</body>') !== false;

            if ($isValidHtml) {
                $this->printDebug('');
                $crawler = new Crawler($body);

                // very specific to Symfony's error page
                $isError = $crawler->filter('#traces-0')->count() > 0
                    || strpos($body, 'looks like something went wrong') !== false;
                if ($isError) {
                    $this->printDebug('There was an Error!!!!');
                    $this->printDebug('');
                } else {
                    $this->printDebug('HTML Summary (h1 and h2):');
                }

                // finds the h1 and h2 tags and prints them only
                foreach ($crawler->filter('h1, h2')->extract(array('_text')) as $header) {
                    // avoid these meaningless headers
                    if (strpos($header, 'Stack Trace') !== false) {
                        continue;
                    }
                    if (strpos($header, 'Logs') !== false) {
                        continue;
                    }

                    // remove line breaks so the message looks nice
                    $header = str_replace("\n", ' ', trim($header));
                    // trim any excess whitespace "foo   bar" => "foo bar"
                    $header = preg_replace('/(\s)+/', ' ', $header);

                    if ($isError) {
                        $this->printErrorBlock($header);
                    } else {
                        $this->printDebug($header);
                    }
                }

                /*
                 * When using the test environment, the profiler is not active
                 * for performance. To help debug, turn it on temporarily in
                 * the config_test.yml file (framework.profiler.collect)
                 */
                $profilerUrl = $response->getHeader('X-Debug-Token-Link');
                if ($profilerUrl) {
                    $fullProfilerUrl = $response->getHeader('Host')[0].$profilerUrl[0];
                    $this->printDebug('');
                    $this->printDebug(sprintf(
                        'Profiler URL: <comment>%s</comment>',
                        $fullProfilerUrl
                    ));
                }

                // an extra line for spacing
                $this->printDebug('');
            } else {
                $this->printDebug($body);
            }
        }
    }

    /**
     * Print a message out - useful for debugging
     *
     * @param $string
     */
    protected function printDebug($string)
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }

        $this->output->writeln($string);
    }

    /**
     * Print a debugging message out in a big red block
     *
     * @param $string
     */
    protected function printErrorBlock($string)
    {
        if ($this->formatterHelper === null) {
            $this->formatterHelper = new FormatterHelper();
        }
        $output = $this->formatterHelper->formatBlock($string, 'bg=red;fg=white', true);

        $this->printDebug($output);
    }

    /**
     * @return RequestInterface
     */
    private function getLastRequest()
    {
        if (!self::$history || empty(self::$history)) {
            return null;
        }

        $history = self::$history;

        $last = array_pop($history);

        return $last['request'];
    }

    protected function createUser($username, $plainPassword = 'foo')
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username.'@foo.com');
        $password = $this->getService('security.password_encoder')
            ->encodePassword($user, $plainPassword);
        $user->setPassword($password);

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function getAuthorizedHeaders($username, $headers = array())
    {
        $token = $this->getService('lexik_jwt_authentication.encoder')
            ->encode([
                'username' => $username,
                'exp' => time() + 3600 // 1 hour expiration
            ]);

        $headers['Authorization'] = 'Bearer '.$token;

        return $headers;
    }


    /**
     * @return ResponseAsserter
     */
    protected function asserter()
    {
        if ($this->responseAsserter === null) {
            $this->responseAsserter = new ResponseAsserter();
        }

        return $this->responseAsserter;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }

    /**
     * Call this when you want to compare URLs in a test
     *
     * (since the returned URL's will have /app_test.php in front)
     *
     * @param string $uri
     * @return string
     */
    protected function adjustUri($uri)
    {
        return '/app_test.php'.$uri;
    }
}
