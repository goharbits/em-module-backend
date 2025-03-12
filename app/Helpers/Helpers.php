<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

function sendSuccess($message, $data = NULL, $code = 200)
{
    //return Response::json(array('status' => 200, 'successMessage' => $message, 'successData' => $data), 200, []);
    return response()->json([
        'status' => $code,
        'message' => $message,
        'data' => $data,
    ]);
}
function sendError($error_message, $data = NULL, $code = 400)
{
    //return Response::json(array('status' => 400, 'errorMessage' => $error_message), 400);
    return response()->json([
        'status' => $code,
        'message' => $error_message,
        'data' => $data,
    ]);
}

function isUser()
{
    if (User::find(Auth::id())->hasRole('user')) {
        return true;
    }

    return false;
}

function isAdmin()
{
    if (User::find(Auth::id())->user_type == 'admin') {
        return true;
    }

    return false;
}

function addFile($file, $path)
{
    if ($file) {
        if ($file->getClientOriginalExtension() != 'exe') {
            $type = $file->getClientMimeType();
            if ($type == 'image/jpg' || $type == 'image/jpeg' || $type == 'image/png' || $type == 'image/bmp') {
                $destination_path = $path;
                $extension = $file->getClientOriginalExtension();
                $fileName = Str::random(15) . '.' . $extension;
                //$img=Image::make($file);
                if (($file->getSize() / 1000) > 2000) {

                    //Image::make($file)->save('public/'.$destination_path. $fileName, 30);
                    $file->save($destination_path . $fileName, 30);
                    $file_path = $destination_path . $fileName;
                } else {
                    $file->move($destination_path, $fileName);
                    $file_path = $destination_path . $fileName;
                }
                return $file_path;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function updateRolesAndPermissions()
{
    Artisan::call('db:seed --class=PermissionSeeder');
    Artisan::call('db:seed --class=RoleSeeder');
}

function generateRandomString($length = 10)
{
    return bin2hex(random_bytes($length));
}

function contactColumns()
{
    return [
        'first_name',
        'last_name',
        'email',
        'title',
        'company',
        'location',
        'industry',
        'parent_industry',
        'lead_status',
    ];
}

function systemModules()
{
    return [
        'lead' => 'Lead',
        'segment' => 'Segment',
        'client' => 'Client',
        'list' => 'List',
        'company' => 'Company',
        'contact' => 'Contact',
        'smtp' => 'SMTP Server',
        'imap' => 'IMAP Server',
        'email_template' => 'Email Template',
        'campaign' => 'Campaign',
        'email_template_type' => 'Email Template Type',
        'user' => 'User',
    ];
}

function createSlug($name)
{
    return Str::slug($name);
}

function getProxyIpUri()
{
    // Set the proxy configuration
    $curl = curl_init('http://ipv4.webshare.io/');
    curl_setopt($curl, CURLOPT_PROXY, 'http://p.webshare.io:80');
    curl_setopt($curl, CURLOPT_PROXYUSERPWD, 'rjhpfotn-rotate:pzg91apphzo2');
    // Execute cURL request
    $proxyIp = curl_exec($curl);
    // Close cURL session
    curl_close($curl);

    // Set proxy configuration for Guzzle HTTP Client
    $proxyUri = "$proxyIp:80";

    return $proxyUri;
}
