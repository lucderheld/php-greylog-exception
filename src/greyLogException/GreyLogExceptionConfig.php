<?php

/*
 * Copyright (C) 2016 luc <luc@def-shop.com>
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
 */

namespace lucderheld\greylogexception;

/**
 * Configuration class for GreyLogException-Class
 * @author luc <luc@def-shop.com>
 */
class GreyLogExceptionConfig {

    /**
     * Configures the IP-Address of the Server the GreyLogException-Class logs to.
     * @var String The IP-Address where the GreyLogException-Class is logging to.
     */
    public static $sGreyLogServerIp = "192.168.1.39";

    /**
     * Configures the facility/name of the application that is writing logs with help of GreyLogException-Class.
     * @var String The application name that is logging the exception (facility in GrayLog).
     */
    public static $sApplicationNameToLog = "SampleApplication";

}
