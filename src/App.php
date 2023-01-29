<?php

namespace MyApp;

use Google\Client;
use Google\Service\Drive;

class App {
    protected array $config;
    protected Drive $service;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function auth()
    {
        session_start();

        $redirectUri = $this->config['redirectUri'];

        $client = new Client();
        $client->setAuthConfig('../' . $this->config['clientSecretFile']);
        $client->setRedirectUri($redirectUri);
        $client->addScope("https://www.googleapis.com/auth/drive");
        
        $service = new Drive($client);

        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $client->setAccessToken($token);

            // store in the session also
            $_SESSION['upload_token'] = $token;

            // redirect back to the example
            header('Location: ' . filter_var($redirectUri, FILTER_SANITIZE_URL));
            exit();
        }

        // set the access token as part of the client
        if (!empty($_SESSION['upload_token'])) {
            $client->setAccessToken($_SESSION['upload_token']);
            if ($client->isAccessTokenExpired()) {
                unset($_SESSION['upload_token']);
                header('Location: ' . filter_var($redirectUri, FILTER_SANITIZE_URL));
                exit();
            }
        }

        if (empty($_SESSION['upload_token'])) {
            $authUrl = $client->createAuthUrl();
            echo "<a class='login' href='{$authUrl}'>Connect Me!</a>";
            exit();
        }

        $this->service = $service;
    }

    public function run()
    {
        $this->auth();
        
        $allowed = ['menu', 'ls', 'compare'];

        $page = $_GET['page'] ?? $allowed[0];
       
        if (!in_array($page, $allowed)) {
            $page = $allowed[0];
        }
        $this->$page();
    }

    protected function ls()
    {
        $my = new GdriveLs($this->service, $this->config['lsFile']);
        $my->run();
        echo "Done!<br/>";
        echo "<a class='login' href='?'>Back</a>";
    }

    protected function compare()
    {
        $my = new Compare();
        foreach ($my->compare('../' . $this->config['dataFile'], '../' . $this->config['lsFile']) as $file) {
            echo $file . "<br/>";
        }
    }

    protected function menu()
    {
        echo "<a href='?page=ls'>Generate ls.txt</a><br/><br/>";
        echo "<a href='?page=compare'>Compare files</a><br/>";
    }
}
