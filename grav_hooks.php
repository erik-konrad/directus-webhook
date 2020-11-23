<?php

class RequestGenerator {
    public function generate($config, $table, $action) {
        return function(array $data) use (&$config, &$table, &$action){
            $client = new \GuzzleHttp\Client([
                'base_uri' => $config['baseUri']
            ]);

            $data = [
                'table' => $table,
                'id' => $data['id'],
            ];

            $response = $client->request('POST', '/' . $config['hookPrefix'] . '/refresh-single', [
                'multipart' => [
                    [
                        'name'     => $action,
                        'contents' => json_encode($data),
                    ]
                ]
            ]);
        };
    }
}

function generateFunctionArray(array $config) {
    $hookArray = [];

    foreach ($config['hooks'] as $action => $tables) {
        foreach ($tables as $table) {
            $requestGenerator = new RequestGenerator();
            $hookArray['item.' . $action . '.' . $table] = $requestGenerator->generate($config, $table, $action);
            unset($requestGenerator);
        }
    }

    return $hookArray;
}

if(file_exists(__DIR__ . '/grav_hooks.json')) {
    $config = json_decode(file_get_contents(__DIR__ . '/grav_hooks.json'), true);

    $requestUrl = $config['baseUri'] . $config['apiRoute'];

    $actionArray['actions'] = generateFunctionArray($config);

    return $actionArray;

}