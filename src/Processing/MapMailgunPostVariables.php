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
use Lasallecms\Lasallecmsapi\Repositories\Traits\PrepareForPersist;
use Lasallecms\Lasallecmsapi\Repositories\UserRepository;

// Laravel classes
use Illuminate\Http\Request;

/**
 * Class MapMailgunPostVariables
 *
 * @package Lasallecms\Lasallecmsmailgun\Processing
 */
class MapMailgunPostVariables
{
    use PrepareForPersist;


    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var Lasallecms\Lasallecmsapi\Repositories\UserRepository
     */
    protected $userRepository;


    /**
     * inboundEmailMailgunController constructor.
     *
     * @param Illuminate\Http\Request                              $request
     * @param Lasallecms\Lasallecmsapi\Repositories\UserRepository $userRepository
     */
    public function __construct(Request $request, UserRepository $userRepository) {
        $this->request        = $request;
        $this->userRepository = $userRepository;
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////
    //                 MAP MAILGUN POST VARS TO email_messages TABLE's FIELDS                     //
    ////////////////////////////////////////////////////////////////////////////////////////////////


    public function mapAlllInboundPostVars() {

        // map Mailgun POST variables to "email_messages" db table fields
        $mapPostVarsToEmail_messagesFields           = $this->mapInboundPostVarsToEmail_messagesFields();

        // map Mailgun POST variables to "email_attachments" db table fields
        $mapInboundPostVarsToEmail_attachmentsFields = $this->mapInboundPostVarsToEmail_attachmentsFields();

        // map Mailgun POST variables to other variables (that are not db fields)
        $mapInboundPostVarsToMiscVars                = $this->mapInboundPostVarsToMiscVars();

        return array_merge(
            $mapPostVarsToEmail_messagesFields,
            $mapInboundPostVarsToEmail_attachmentsFields,
            $mapInboundPostVarsToMiscVars
        );
    }





    /**
     * Map the non-attachment vars from the inbound email webhook to the email_messages fields
     *
     * @return array
     */
    public function mapInboundPostVarsToEmail_messagesFields() {

        $data = [];

        $data['user_id']            = $this->getUserIdByMappedEmailAddress();

        $data['from_email_address'] = trim($this->request->input('sender'));
        $data['from_name']          = $this->genericWashText($this->request ->input('from'));
        $data['to_email_address']   = $this->setToEmailAddressField();
        $data['to_name']            = $this->setToField();
        $data['subject']            = $this->genericWashText($this->request->input('subject'));
        $data['body']               = $this->setBodyField();
        $data['message_header']     = json_decode($this->request->input('message-headers'));
        $data['recipient']          = $this->request->input('recipient');

        return $data;
    }

    /**
     * Get the user's id (from the "users" table) using the mapped email address
     *
     * @return int
     */
    public function getUserIdByMappedEmailAddress() {

        // get the mapped recipient (valid because $this->isInboundEmailToEmailAddressMapToUser() is already done)
        $mappedRoutes = config('lasallecmsmailgun.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                $userEmailAddress = $user;
            }
        }

        return $this->userRepository->findUserIdByEmail($userEmailAddress);
    }

    /**
     * Set the "to_email_address" db field
     *
     * @return string
     */
    public function setToEmailAddressField() {

        // get the mapped recipient (valid because $this->isInboundEmailToEmailAddressMapToUser() is already done)
        $mappedRoutes = config('lasallecmsmailgun.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                return $user;
            }
        }
    }

    /**
     * Set the "to_email_address" db field
     *
     * @return string
     */
    public function setToField() {

        // get the mapped recipient (valid because $this->isInboundEmailToEmailAddressMapToUser() is already done)
        $mappedRoutes = config('lasallecmsmailgun.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                $userEmailAddress = $user;
            }
        }

        return $this->userRepository->findUserNameByEmail($userEmailAddress);
    }

    /**
     * Set the email's body field
     *
     * @return mixed
     */
    public function setBodyField() {

        if ($this->request->input('stripped-html')) {
            return $this->request->input('stripped-html');
        }

        return $this->request->input('body-plain');
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////
    //              MAP MAILGUN POST VARS TO email_attachments TABLE's FIELDS                     //
    ////////////////////////////////////////////////////////////////////////////////////////////////

    public function mapInboundPostVarsToEmail_attachmentsFields() {

        $data = [];

        if ($this->request->input('attachment-count') == 0) {
            $data['number_of_attachments'] = 0;
            return $data;
        }

        // Attachments exist
        $data['number_of_attachments']    = $this->request->input('attachment-count');

        // INSERT into the "email_attachments" db table
        for ($i = 1; $i <= $data['number_of_attachments']; $i++) {
            $data['attachment-'.$i] = $this->request->file('attachment-'.$i);
        }

        return $data;
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////
    //                        MAP MAILGUN POST VARS TO NON DB TABLE VARS                          //
    ////////////////////////////////////////////////////////////////////////////////////////////////

    public function mapInboundPostVarsToMiscVars() {

        return ['misc_var' => 'none'];
    }




}