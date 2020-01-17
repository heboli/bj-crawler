<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

class ScrapeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take the CSRF Token from BJ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tokenClient = new Client(HttpClient::create(['timeout' => 600]));
        $crawler = $tokenClient->request('GET', 'https://portal.brasiljunior.org.br/entrar');
        $utf8 = urlencode($crawler->filterXpath("//input[@name='utf8']")->extract(array('value'))[0]);
        $csrf_token = urlencode($crawler->filterXpath("//meta[@name='csrf-token']")->extract(array('content'))[0]);
        $email = urlencode("hebertoliveira1@hotmail.com");
        $password = urlencode("brasiljunior");
        // print $csrf_token . $utf8 ."\n";
        print "utf8=" . $utf8 ."&authenticity_token=" . $csrf_token . "&brasiljunior_user%5Bemail%5D=" . $email . "&brasiljunior_user%5Bpassword%5D=" . $password ;
        print "\n";

        $client = HttpClient::create(['headers' => [
            'User-Agent' => 'My Fancy App',
        ]]);
        $response = $client->request('POST', 'https://portal.brasiljunior.org.br/entrar', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Origin' => 'https://portal.brasiljunior.org.br'
            ],
            'body' => "utf8=" . $utf8 ."&authenticity_token=" . $csrf_token . "&brasiljunior_user%5Bemail%5D=" . $email . "&brasiljunior_user%5Bpassword%5D=" . $password,
        ]);
        // print ($response->getHeaders()['set-cookie'][0]);
        $sesion_id = $response->getHeaders()['set-cookie'][0];
        print json_encode($response->getStatusCode()) . "\n";
        print $sesion_id . "\n";
        $response2 = $client->request('GET', 'https://portal.brasiljunior.org.br/api/v1/admin/ejs/cimatec-jr/dashboard', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Origin' => 'https://portal.brasiljunior.org.br',
                'Cookie' => $sesion_id
            ],
        ]);
        print json_encode($response2->getHeaders());
    }
}
