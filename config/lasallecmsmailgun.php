<?php

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


return [

    /*
    |--------------------------------------------------------------------------
    | Map inbound Mailgun route with "users" email address
    |--------------------------------------------------------------------------
    |
    | An email is sent. The MX record results in the email being sent to Mailgun.
    | Mailgun looks at the inbound routes that we set up, and then sends it to
    | our web application.
    |
    | What I am assuming here is that there is one Mailgun route (that's what Mailgun calls 'em, "routes")
    | for one email in the LaSalle Software "users" table (which is the db table Laravel -- and LaSalle Software.
    | uses -- for auth).
    |
    | So, one recipient for each incoming Mailgun route maps to one record in the "users" table (by email address).
    |
    |
    */
    'inbound_map_mailgun_routes_with_user_email_address' => [
        'custom@emailtx.retroradioes.com'   => 'info@southlasalle.com',
    ],

];
