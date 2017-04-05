<?php
/*
 * Fleet is a program whose purpose is to manage a fleet of mobile devices.
 * Copyright (C) 2016-2017 Vincent Primault <vincent.primault@liris.cnrs.fr>
 *
 * Fleet is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Fleet is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fleet.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Privamov\Auth;

/**
 * Authenticate users through a LDAP directory.
 *
 * @author Vincent Primault <vincent.primault@liris.cnrs.fr>
 */
class LdapAuthHandler implements AuthHandler
{
    private $server;
    private $baseDn;
    private $loginAttr;
    private $usernameAttr;
    private $allowed;

    /**
     * Constructor.
     *
     * @param string $server LDAP server URL
     * @param string $baseDn Base dn where to find users
     * @param string $loginAttr LDAP attribute storing login
     * @param string $usernameAttr LDAP attribute storing username
     * @param array $allowed A list of allowed logins, empty to allow every valid LDAP user
     */
    public function __construct($server, $baseDn, $loginAttr, $usernameAttr, array $allowed = [])
    {
        $this->server = $server;
        $this->baseDn = $baseDn;
        $this->loginAttr = $loginAttr;
        $this->usernameAttr = $usernameAttr;
        $this->allowed = $allowed;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($login, $password)
    {
        if (!empty($this->allowed) && !in_array($login, $this->allowed)) {
            return false;
        }

        // Disable server certificate verification. Not ideal, but sufficient for us...
        putenv('LDAPTLS_REQCERT=never');
        putenv('TLS_CACERTDIR=/etc/openldap/certs');
        $ldap = ldap_connect($this->server);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $dn = sprintf('%s=%s,%s', $this->loginAttr, $login, $this->baseDn);
        if (@ldap_bind($ldap, $dn, $password)) {
            $res = ldap_search($ldap, $this->baseDn, sprintf('(%s=%s)', $this->loginAttr, $login), [$this->usernameAttr]);
            if (!$res) {
                $errno = ldap_errno($ldap);
                throw new AuthException('LDAP search error: (' . $errno . ') ' . ldap_err2str($errno));
            }
            $entries = ldap_get_entries($ldap, $res);
            if ($entries['count'] !== 1) {
                throw new AuthException('Unexpected entries count: found ' . $entries['count'] . ', expecting 1.');
            }

            return ['username' => $entries[0][$this->usernameAttr][0]];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomain()
    {
        return substr($this->server, strpos($this->server, '://') + 3);
    }
}
