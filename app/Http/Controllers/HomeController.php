<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $username = 'orthanc';
        $password = 'orthanc';

        $client = new Client();
        $studiesResponse = $client->request('GET', 'http://192.168.1.225:8042/studies', [
//        $studiesResponse = $client->request('GET', 'http://192.168.1.225:8042/instances/331add13-f68a80fe-0bee7b21-13aa3233-4ea56edd', [
            'auth' => [$username, $password],

        ]);
//        9d00089c-4194a6c2-1c2d95f4-32a2777d-ac29d24e

        $statusCode = $studiesResponse->getStatusCode();
        $studies = $studiesResponse->getBody()->getContents();

//        dd($studies);
        if($statusCode == 200){
            $studies = json_decode($studies,true);
            if(count($studies)){
                foreach ($studies as $study){
                    $client = new Client();
                    $studiResponse = $client->request('GET', 'http://192.168.1.225:8042/studies/331add13-f68a80fe-0bee7b21-13aa3233-4ea56edd', [
                        'auth' => [$username, $password],

                    ]);
                    $statusCode = $studiResponse->getStatusCode();
                    $studyData = $studiResponse->getBody()->getContents();

                    //get instances
                    if($statusCode == 200){
                        $client = new Client();
                        $innstancesResponse = $client->request('GET', 'http://192.168.1.225:8042/studies/331add13-f68a80fe-0bee7b21-13aa3233-4ea56edd/instances', [
                            'auth' => [$username, $password],

                        ]);
                        $instancesStatusCode = $innstancesResponse->getStatusCode();
                        $instanceData = $innstancesResponse->getBody()->getContents();


                        //download file
                        if($instancesStatusCode == 200){
                            $instanceData = json_decode($instanceData,true);
                            foreach ($instanceData as $instanceDatum){
                                $client = new Client();
                                $innstancesResponse = $client->request('GET', 'http://192.168.1.225:8042/instances/'.$instanceDatum['ID'].'/file', [
                                    'auth' => [$username, $password],

                                ]);
                                $instancesStatusCode = $innstancesResponse->getStatusCode();
                                $fileContent = $innstancesResponse->getBody()->getContents();

                                Storage::disk('local')->put($instanceDatum['FileUuid'].'.dcm', $fileContent);
                                dd('file',count($instanceData),$instanceDatum,$fileContent);
                            }

                        }
                        dd('instances',$studyData,$instanceData,'info');

                    }

//                    /instances/{id}/file
                }
            }
            dd($studies,11111);
        }
//        $response = $client->request('GET', 'http://212.73.91.18:8042/', [
//            'auth' => [$username, $password]
//        ]);



        dd($studies,$statusCode,55);
        return view('home');
    }
}
