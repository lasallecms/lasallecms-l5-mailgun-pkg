<?php

namespace Lasallecms\Lasallecmsmailgun\Processing;

/**
 *
 * Mailgun package for the LaSalle Content Management System, based on the Laravel 5 Framework
 * Copyright (C) 2015 - 2016  The South LaSalle Trading Corporation
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @package    Mailgun package for the LaSalle Content Management System
 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\UserRepository;

// Laravel classes
use Illuminate\Http\Request;

/**
 * Class Validation
 * @package Lasallecms\Lasallecmsmailgun\Processing
 */
class Validation
{
    /**
     * @var Lasallecms\Lasallecmsapi\Repositories\UserRepository
     */
    protected $userRepository;

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;


    /**
     * inboundEmailMailgunController constructor.
     *
     * @param Lasallecms\Lasallecmsapi\Repositories\UserRepository  $userRepository
     * @param Illuminate\Http\Request                                $request
     */
    public function __construct(UserRepository  $userRepository, Request $request) {
        $this->userRepository =  $userRepository;
        $this->request        = $request;
    }


    /**
     * Ensure the authenticity of inbound Mailgun request
     *
     * https://documentation.mailgun.com/user_manual.html#webhooks
     * https://github.com/mailgun/mailgun-php/blob/master/src/Mailgun/Mailgun.php
     * http://php.net/manual/en/function.hash-hmac.php
     *
     * @param  timestamp  $timestamp  Mailgun's timestamp in the POST request
     * @param  string     $token      Mailguns's token in the POST request
     * @paraam string     $signature  Mailgun's signature in the POST request
     * @return bool
     */
    public function verifyWebhookSignature() {

        $timestamp = $this->request->input('timestamp');
        $token     = $this->request->input('token');
        $signature = $this->request->input('signature');


        // The Mailgun config param is an array, so grab the full array
        $configMailgun = config('services.mailgun');

        $hmac = hash_hmac('sha256', $timestamp. $token, $configMailgun['secret']);

        if(function_exists('hash_equals')) {

            // hash_equals is constant time, but will not be introduced until PHP 5.6
            return hash_equals($hmac, $signature);
        }

        return ($hmac == $signature);
    }

    /**
     * Does the recipient's email address map to an email address in the "users" database table?
     *
     * @return bool
     */
    public function isInboundEmailToEmailAddressMapToUser() {

        // We map an inbound Mailgun route to a record in the "users" table, by email address
        $mappedRoutes = config('lasallecmsmailgun.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does the mapped user actually exist in the "users" db table?
     *
     * @return bool
     */
    public function isMappedUserExistInUsersTable() {

        // get the mapped recipient (valid because $this->isInboundEmailToEmailAddressMapToUser() is already done)
        $mappedRoutes = config('lasallecmsmailgun.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                $userEmailAddress = $user;
            }
        }

        if ($this->userRepository->findUserIdByEmail($userEmailAddress)) {
            return true;
        }

        return false;
    }


}
