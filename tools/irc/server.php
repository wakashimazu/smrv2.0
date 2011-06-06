<?php

// very important
// if we do not answer the ping from server we will be disconnected
function server_ping($fp, $rdata)
{

    if (preg_match('/^PING\s:(.*)\s/i', $rdata, $msg)) {

        $server = $msg[1];

        echo_r('[PING] from ' . $server);

        fputs($fp, 'PONG ' . $server . EOL);
        return true;
    }

    return false;

}

// part of a whois msg
function server_msg_307($fp, $rdata)
{

    // :ice.coldfront.net 307 Caretaker MrSpock :is a registered nick
    if (preg_match('/^:(.*) 307 Caretaker (.*) :is a registered nick\s/i', $rdata, $msg)) {

        $server = $msg[1];
        $nick = $msg[2];

        echo_r('[SERVER_307] ' . $server . ' said that ' . $nick . ' is registered');

        $db = new SmrMySqlDatabase();
        $db2 = new SmrMySqlDatabase();

        $db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));
        while ($db->nextRecord()) {
            $seen_id = $db->getField('seen_id');

            $db2->query('UPDATE irc_seen SET ' .
                        'registered = 1 ' .
                        'WHERE seen_id = ' . $seen_id);
        }

        return true;
    }

    return false;

}

// end of whois list
function server_msg_318($fp, $rdata)
{

    // :ice.coldfront.net 318 Caretaker MrSpock :End of /WHOIS list.
    if (preg_match('/^:(.*) 318 Caretaker (.*) :End of \/WHOIS list\.\s/i', $rdata, $msg)) {

        $server = $msg[1];
        $nick = $msg[2];

        echo_r('[SERVER_318] ' . $server . ' end of /WHOIS for ' . $nick);

        $db = new SmrMySqlDatabase();
        $db2 = new SmrMySqlDatabase();

        $db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND registered IS NULL');
        while ($db->nextRecord()) {
            $seen_id = $db->getField('seen_id');

            $db2->query('UPDATE irc_seen SET ' .
                        'registered = 0 ' .
                        'WHERE seen_id = ' . $seen_id);
        }

        return true;
    }

    return false;

}

// response to WHO
function server_msg_352($fp, $rdata)
{

	// :ice.coldfront.net 352 Caretaker #KMFDM caretaker coldfront-425DB813.dip.t-dialin.net ice.coldfront.net Caretaker Hr :0 Official SMR bot
    if (preg_match('/^:(.*?) 352 Caretaker #(.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?)$/i', $rdata, $msg)) {

        $server = $msg[1];
        $channel = $msg[2];
        $user = $msg[3];
        $host = $msg[4];
        $nick = $msg[6];

        echo_r('[WHO] #' . $channel . ': ' . $nick);

        $db = new SmrMySqlDatabase();

        // check if we have seen this user before
        $db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND channel = ' . $db->escapeString($channel));

        if ($db->nextRecord()) {
            // exiting nick?
            $seen_id = $db->getField('seen_id');

            $db->query('UPDATE irc_seen SET ' .
                       'signed_on = ' . time() . ', ' .
                       'signed_off = 0, ' .
                       'user = ' . $db->escapeString($user) . ', ' .
                       'host = ' . $db->escapeString($host) . ', ' .
                       'registered = NULL ' .
                       'WHERE seen_id = ' . $seen_id);

        } else {
            // new nick?
            $db->query('INSERT INTO irc_seen (nick, user, host, channel, signed_on) ' .
                       'VALUES(' . $db->escapeString($nick) . ', ' . $db->escapeString($user) . ', ' . $db->escapeString($host) . ', ' . $db->escapeString($channel) . ', ' . time() . ')');
        }

	    sleep(1);
	    fputs($fp, 'WHOIS ' . $nick . EOL);

        return true;

    }

    return false;

}

// unknown user
function server_msg_401($fp, $rdata)
{

    // :ice.coldfront.net 401 Caretaker MrSpock :No such nick/channel
    if (preg_match('/^:(.*) 401 Caretaker (.*) :No such nick\/channel\s/i', $rdata, $msg)) {

        $server = $msg[1];
        $nick = $msg[2];

        echo_r('[SERVER_401] ' . $server . ' said: "No such nick/channel" for ' . $nick);

        $db = new SmrMySqlDatabase();
        $db2 = new SmrMySqlDatabase();

        // get the user in question
        $db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick) . ' AND signed_off = 0');
        if ($db->nextRecord()) {
            $seen_id = $db->getField('seen_id');

            // maybe he left without us noticing, so we fix this now
            $db->query('UPDATE irc_seen SET ' .
                       'signed_off = ' . time() . ', ' .
                       'WHERE seen_id = ' . $seen_id);

        }

        return true;
    }

    return false;

}

?>