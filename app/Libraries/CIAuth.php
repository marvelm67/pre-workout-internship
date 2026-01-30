<?php namespace App\Libraries;

class CIAuth
{
    public static function setCIAuth($result)
    {
        $session = session();
        $array = ['logged_in' => true,
                    'username'  => $result->username,
                    'email'     => $result->email,
                    'role'      => $result->role,
                    'user_id'   => $result->id
                ];
        $userdata = $result;
        $session->set(data: 'userdata', value: $userdata);
        $session->set($array);
    }

    public static function id(){
        $session = session();
        if ($session->has('logged_in')) {
            if ($session->has('userdata')) {
                return $session->get('user_data')('id');
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public static function check(){
        $session = session();
        return $session->has('logged_in');
    }

    public static function forget(){
        $session = session();
        $session->remove('logged_in');
        $session->remove('user_data');
    }

    public static function user(){
        $session = session();
        if ($session->has('logged_in')) {
            if ($session->has('user_data')) {
                return $session->get('user_data');
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}

