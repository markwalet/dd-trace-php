--TEST--
Extract client IP address (no ip header set)
--FILE--
<?php
function test($header, $value) {
    echo "$header: $value\n";
    $res = DDTrace\extract_ip_from_headers(['HTTP_' . strtoupper($header) => $value]);
    $resRaw = DDTrace\extract_ip_from_headers([strtr($header, "_", "-") => $value]);
    if ($res !== $resRaw) {
        var_dump($res, $resRaw);
    } elseif (array_key_exists('http.client_ip', $res)) {
        var_dump($res['http.client_ip']);
    } else {
        echo "NULL\n";
    }
    echo "\n";
}

test('x_forwarded_for', '2001::1');
test('x_forwarded_for', '::1, febf::1, fc00::1, fd00::1,2001:0000::1');
test('x_forwarded_for', '172.16.0.1');
test('x_forwarded_for', '172.16.0.1, 172.31.255.254, 172.32.255.1, 8.8.8.8, 1.2.3.4:456, [2001::1]:1111');
test('x_forwarded_for', '169.254.0.1, 127.1.1.1, 10.255.255.254,');
test('x_forwarded_for', '127.1.1.1,, ');
test('x_forwarded_for', '1.2.3.4:456');
test('x_forwarded_for', '[2001::1]:1111');
test('x_forwarded_for', 'bad_value, 1.1.1.1');

test('x_real_ip', '2.2.2.2');
test('x_real_ip', '2.2.2.2, 3.3.3.3');
test('x_real_ip', '127.0.0.1');
test('x_real_ip', '::ffff:4.4.4.4');
test('x_real_ip', '::ffff:127.0.0.1');
test('x_real_ip', 42);
test('x_real_ip', 'fec0::1');
test('x_real_ip', 'fe80::1');
test('x_real_ip', 'fd00::1');
test('x_real_ip', 'fc22:11:22:33::1');
test('x_real_ip', 'fd12:3456:789a:1::1');

test('x_forwarded', 'for="[2001::1]:1111"');
test('x_forwarded', 'fOr="[2001::1]:1111"');
test('x_forwarded', 'for="2001:abcf::1"');
test('x_forwarded', 'for=some_host');
test('x_forwarded', 'for=127.0.0.1, FOR=1.1.1.1');
test('x_forwarded', 'for="\"foobar";proto=http,FOR="1.1.1.1"');
test('x_forwarded', 'for="8.8.8.8:2222",');
test('x_forwarded', 'for="8.8.8.8'); // quote not closed
test('x_forwarded', 'far="8.8.8.8",for=4.4.4.4;');
test('x_forwarded', '   for=127.0.0.1,for= for=,for=;"for = for="" ,; for=8.8.8.8;');

test('forwarded_for', '::1, 127.0.0.1, 2001::1');

test('true_client_ip', '8.8.8.8');

test('fastly_client_ip', '2.2.2.2');
test('fastly_client_ip', '2.2.2.2, 3.3.3.3');
test('fastly_client_ip', '127.0.0.1');
test('fastly_client_ip', '::ffff:4.4.4.4');
test('fastly_client_ip', '::ffff:127.0.0.1');
test('fastly_client_ip', 42);
test('fastly_client_ip', 'fec0::1');
test('fastly_client_ip', 'fe80::1');
test('fastly_client_ip', 'fd00::1');
test('fastly_client_ip', 'fc22:11:22:33::1');
test('fastly_client_ip', 'fd12:3456:789a:1::1');

test('cf_connecting_ip', '2.2.2.2');
test('cf_connecting_ip', '2.2.2.2, 3.3.3.3');
test('cf_connecting_ip', '127.0.0.1');

test('cf_connecting_ipv6', '::ffff:4.4.4.4');
test('cf_connecting_ipv6', '::ffff:127.0.0.1');
test('cf_connecting_ipv6', 42);
test('cf_connecting_ipv6', 'fec0::1');
test('cf_connecting_ipv6', 'fe80::1');
test('cf_connecting_ipv6', 'fd00::1');
test('cf_connecting_ipv6', 'fc22:11:22:33::1');
test('cf_connecting_ipv6', 'fd12:3456:789a:1::1');

echo "remote address fallback: 8.8.8.8\n";
var_dump(DDTrace\extract_ip_from_headers(['REMOTE_ADDR' => '8.8.8.8'])['http.client_ip']);

?>
--EXPECTF--
x_forwarded_for: 2001::1
string(7) "2001::1"

x_forwarded_for: ::1, febf::1, fc00::1, fd00::1,2001:0000::1
string(7) "2001::1"

x_forwarded_for: 172.16.0.1
NULL

x_forwarded_for: 172.16.0.1, 172.31.255.254, 172.32.255.1, 8.8.8.8, 1.2.3.4:456, [2001::1]:1111
string(12) "172.32.255.1"

x_forwarded_for: 169.254.0.1, 127.1.1.1, 10.255.255.254,
NULL

x_forwarded_for: 127.1.1.1,, 
NULL

x_forwarded_for: 1.2.3.4:456
string(7) "1.2.3.4"

x_forwarded_for: [2001::1]:1111
string(7) "2001::1"

x_forwarded_for: bad_value, 1.1.1.1
Not recognized as IP address: "bad_value"
Not recognized as IP address: "bad_value"
string(7) "1.1.1.1"

x_real_ip: 2.2.2.2
string(7) "2.2.2.2"

x_real_ip: 2.2.2.2, 3.3.3.3
Not recognized as IP address: "2.2.2.2, 3.3.3.3"
Not recognized as IP address: "2.2.2.2, 3.3.3.3"
NULL

x_real_ip: 127.0.0.1
NULL

x_real_ip: ::ffff:4.4.4.4
string(7) "4.4.4.4"

x_real_ip: ::ffff:127.0.0.1
NULL

x_real_ip: 42
NULL

x_real_ip: fec0::1
NULL

x_real_ip: fe80::1
NULL

x_real_ip: fd00::1
NULL

x_real_ip: fc22:11:22:33::1
NULL

x_real_ip: fd12:3456:789a:1::1
NULL

x_forwarded: for="[2001::1]:1111"
string(7) "2001::1"

x_forwarded: fOr="[2001::1]:1111"
string(7) "2001::1"

x_forwarded: for="2001:abcf::1"
string(12) "2001:abcf::1"

x_forwarded: for=some_host
Not recognized as IP address: "some_host"
Not recognized as IP address: "some_host"
NULL

x_forwarded: for=127.0.0.1, FOR=1.1.1.1
string(7) "1.1.1.1"

x_forwarded: for="\"foobar";proto=http,FOR="1.1.1.1"
Not recognized as IP address: "\"foobar"
Not recognized as IP address: "\"foobar"
string(7) "1.1.1.1"

x_forwarded: for="8.8.8.8:2222",
string(7) "8.8.8.8"

x_forwarded: for="8.8.8.8
NULL

x_forwarded: far="8.8.8.8",for=4.4.4.4;
string(7) "4.4.4.4"

x_forwarded:    for=127.0.0.1,for= for=,for=;"for = for="" ,; for=8.8.8.8;
string(7) "8.8.8.8"

forwarded_for: ::1, 127.0.0.1, 2001::1
string(7) "2001::1"

true_client_ip: 8.8.8.8
string(7) "8.8.8.8"

fastly_client_ip: 2.2.2.2
string(7) "2.2.2.2"

fastly_client_ip: 2.2.2.2, 3.3.3.3
Not recognized as IP address: "2.2.2.2, 3.3.3.3"
Not recognized as IP address: "2.2.2.2, 3.3.3.3"
NULL

fastly_client_ip: 127.0.0.1
NULL

fastly_client_ip: ::ffff:4.4.4.4
string(7) "4.4.4.4"

fastly_client_ip: ::ffff:127.0.0.1
NULL

fastly_client_ip: 42
NULL

fastly_client_ip: fec0::1
NULL

fastly_client_ip: fe80::1
NULL

fastly_client_ip: fd00::1
NULL

fastly_client_ip: fc22:11:22:33::1
NULL

fastly_client_ip: fd12:3456:789a:1::1
NULL

cf_connecting_ip: 2.2.2.2
string(7) "2.2.2.2"

cf_connecting_ip: 2.2.2.2, 3.3.3.3
Not recognized as IP address: "2.2.2.2, 3.3.3.3"
Not recognized as IP address: "2.2.2.2, 3.3.3.3"
NULL

cf_connecting_ip: 127.0.0.1
NULL

cf_connecting_ipv6: ::ffff:4.4.4.4
string(7) "4.4.4.4"

cf_connecting_ipv6: ::ffff:127.0.0.1
NULL

cf_connecting_ipv6: 42
NULL

cf_connecting_ipv6: fec0::1
NULL

cf_connecting_ipv6: fe80::1
NULL

cf_connecting_ipv6: fd00::1
NULL

cf_connecting_ipv6: fc22:11:22:33::1
NULL

cf_connecting_ipv6: fd12:3456:789a:1::1
NULL

remote address fallback: 8.8.8.8
string(7) "8.8.8.8"
