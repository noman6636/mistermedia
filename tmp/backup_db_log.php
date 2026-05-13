<div style='background-color:#AFF'><h3>Encryption</h3><p><strong>PHP Version</strong></p><pre>8.1.34</pre><hr /><p><strong>Cryptor</strong></p><pre>OpenSSL</pre><hr /><p><strong>Default Cipher</strong></p><pre>aes-256-cbc</pre><hr /><p><strong>mb_internal_encoding</strong></p><pre>UTF-8</pre><hr /><div style='background-color:#AAA'><h3>IP Validation</h3><p><strong>$headers from sl_get_ip()</strong></p><pre>Array
(
    [Connection] => TE
    [Host] => mistermediasolution.com
    [User-Agent] => SiteLock (Module: SmartDB; Source: https://www.sitelock.com/; Version: 1.0)
    [x-forwarded-proto] => https
    [x-https] => on
    [X-Forwarded-For] => 184.154.76.40
)
</pre><hr /><p><strong>IP Check started in</strong></p><pre>/home/mistgzny/public_html/tmp/7f60836deb74fed9e70e3937f15bc82b.php</pre><hr /><p><strong>IP Check started at</strong></p><pre>2026-05-13T13:25:14-04:00</pre><hr /><p><strong>The following IPs will be tested</strong></p><pre>Array
(
    [0] => 184.154.76.40
)
</pre><hr /><p><strong>mapi_post URL</strong></p><pre>https://mapi.sitelock.com/v3/connect/</pre><hr /><p><strong>mapi_post_request</strong></p><pre>Array
(
    [pluginVersion] => 100.0.0
    [apiTargetVersion] => 3.0.0
    [token] => 713***
    [requests] => Array
        (
            [id] => 6edd0bdbc2b643edab66d7b23d17729d-17786931146123
            [action] => validate_ip
            [params] => Array
                (
                    [site_id] => 48126056
                    [ip] => 184.154.76.40
                )

        )

)
</pre><hr /><p><strong>curl_getinfo()</strong></p><pre>Array
(
    [url] => https://mapi.sitelock.com/v3/connect/
    [content_type] => text/html; charset=UTF-8
    [http_code] => 200
    [header_size] => 766
    [request_size] => 502
    [filetime] => -1
    [ssl_verify_result] => 20
    [redirect_count] => 0
    [total_time] => 0.338417
    [namelookup_time] => 0.00017
    [connect_time] => 0.032864
    [pretransfer_time] => 0.066202
    [size_upload] => 324
    [size_download] => 518
    [speed_download] => 1530
    [speed_upload] => 957
    [download_content_length] => -1
    [upload_content_length] => 324
    [starttransfer_time] => 0.338391
    [redirect_time] => 0
    [redirect_url] => 
    [primary_ip] => 45.60.12.54
    [certinfo] => Array
        (
        )

    [primary_port] => 443
    [local_ip] => 162.254.39.161
    [local_port] => 47058
    [http_version] => 2
    [protocol] => 2
    [ssl_verifyresult] => 0
    [scheme] => https
    [appconnect_time_us] => 66119
    [connect_time_us] => 32864
    [namelookup_time_us] => 170
    [pretransfer_time_us] => 66202
    [redirect_time_us] => 0
    [starttransfer_time_us] => 338391
    [total_time_us] => 338417
)
</pre><hr /><p><strong>mapi_response</strong></p><pre><textarea style="width:99%;height:100px;">{"apiVersion":"3.0.1","status":"ok","globalResponse":null,"banner":null,"forceLogout":false,"newToken":null,"now":1778693114,"responses":[{"id":"6edd0bdbc2b643edab66d7b23d17729d-17786931146123","data":{"ip_address":"184.154.76.40","valid":true},"raw_api_url":"https:\/\/api.sitelock.com\/v1\/dbscan\/checkip","raw_response":{"@attributes":{"version":"1.1","encoding":"UTF-8"},"checkIP":{"status":"1"}},"raw_request":{"site_id":"48126056","ip":"184.154.76.40"},"user_agent":"SiteLock Bullet for Backup","status":"ok"}]}</textarea></pre><hr /><pre>Ifsnop\Mysqldump is loaded into memory.</pre><hr /><div style='background-color:#AFA'><h3>BackupGrabAndZip</h3><p><strong>_POST</strong></p><pre>Array
(
)
</pre><hr /><p><strong>_GET (raw)</strong></p><pre>cmd=db_creds_ready&enc_db_creds=urULwQk9FwN%2FwDyJx5OU4e4L5BEJ%2BaODMpQmTTZjvgdOsOckG5cp6hxa2Xb%2B%2Bl%2BLQRMP33n5BEzYfGi%2FxfiFaNXiei413aI8%2BeqhMmwYij6aus9sUkm9lU1ERsoO8GUGTW3pEoVziqN5kpRUujnlycG2Mcb4%2B%2FttiYJn1NUf6Jc%3D&smart_single_download_id=5658578</pre><hr /><p><strong>Detected memory_limit</strong></p><pre>10240M</pre><hr /><p><strong>Chunk Size</strong></p><pre>10485760</pre><hr /><p><strong>mapi_post URL</strong></p><pre>https://mapi.sitelock.com/v3/connect/</pre><hr /><p><strong>mapi_post_request</strong></p><pre>Array
(
    [pluginVersion] => 100.0.0
    [apiTargetVersion] => 3.0.0
    [token] => 713***
    [requests] => Array
        (
            [id] => 10aa8ccf964fe69a21caaee64a23755c-17786931149512
            [action] => s3_get_enc_info
            [params] => Array
                (
                    [site_id] => 48126056
                    [queue_id] => 5658578
                )

        )

)
</pre><hr /><p><strong>curl_getinfo()</strong></p><pre>Array
(
    [url] => https://mapi.sitelock.com/v3/connect/
    [content_type] => text/html; charset=UTF-8
    [http_code] => 200
    [header_size] => 766
    [request_size] => 506
    [filetime] => -1
    [ssl_verify_result] => 20
    [redirect_count] => 0
    [total_time] => 0.298347
    [namelookup_time] => 0.000586
    [connect_time] => 0.032157
    [pretransfer_time] => 0.067934
    [size_upload] => 328
    [size_download] => 825
    [speed_download] => 2765
    [speed_upload] => 1099
    [download_content_length] => -1
    [upload_content_length] => 328
    [starttransfer_time] => 0.298323
    [redirect_time] => 0
    [redirect_url] => 
    [primary_ip] => 45.60.12.54
    [certinfo] => Array
        (
        )

    [primary_port] => 443
    [local_ip] => 162.254.39.161
    [local_port] => 47064
    [http_version] => 2
    [protocol] => 2
    [ssl_verifyresult] => 0
    [scheme] => https
    [appconnect_time_us] => 67850
    [connect_time_us] => 32157
    [namelookup_time_us] => 586
    [pretransfer_time_us] => 67934
    [redirect_time_us] => 0
    [starttransfer_time_us] => 298323
    [total_time_us] => 298347
)
</pre><hr /><p><strong>Received encryption details</strong></p><pre>Array
(
    [cipher] => aes-256-cbc
    [key] => NHE***
    [iv] => FlY***
)
</pre><hr /><p><strong>sl_get_bullet_lock_path:</strong></p><pre>/home/mistgzny/public_html/tmp/7f60836deb74fed9e70e3937f15bc82b.php.lock</pre><hr /><p><strong>sl_is_bullet_locked check:</strong></p><pre>not locked (no lock file)</pre><hr /><p><strong>sl_get_bullet_lock_path:</strong></p><pre>/home/mistgzny/public_html/tmp/7f60836deb74fed9e70e3937f15bc82b.php.lock</pre><hr /><p><strong>sl_lock_the_bullet: bytes written</strong></p><pre>10</pre><hr /><p><strong>Starting MySQLi constructor</strong></p><pre></pre><hr /><p><strong>MySQL Version Detected</strong></p><pre>11.4.10-MariaDB-cll-lve</pre><hr /><p><strong>$SL_SCHEMAS received</strong></p><pre><textarea style="width:99%;height:100px;">["mistgzny_mistermediasolutions"]</textarea></pre><hr /><p><strong>Attempted to setlocale() with UTF-8</strong></p><pre>Success</pre><hr /><p><strong>establish_mysql_version()</strong></p><pre>mysql from 11.4.10-MariaDB, client 15.2 for Linux (x86_64) using readline 5.1</pre><hr /><p><strong>Trying sed.</strong></p><pre> | /bin/sed -r -e 's/DEFINER=`[^`]+`@`[^`]+`//'</pre><hr /><p><strong>/bin/mysqldump Version</strong></p><pre>/bin/mysqldump from 11.4.10-MariaDB, client 10.19 for Linux (x86_64)</pre><hr /><p><strong>mysqldump: success</strong></p><pre>/bin/mysqldump --defaults-file=/tmp/sl-mysqldumpdl8yqI -hbusiness165.web-hosting.com  --port=3306 --quick --compact --skip-comments --events --routines --create-options --add-drop-table --add-drop-trigger --force --no-tablespaces --databases 'mistgzny_mistermediasolutions'  | /bin/sed -r -e 's/DEFINER=`[^`]+`@`[^`]+`//'  | zip -jqm1 ./.d943d10c313cc7a4b77c5e4b280f8163/everything.sql.zip -</pre><hr /><p><strong>zipnote: success</strong></p><pre>zipnote -w ./.d943d10c313cc7a4b77c5e4b280f8163/everything.sql.zip <<<$'@ -
@=database_backup.sql'</pre><hr /><p><strong>Original ZIP size</strong></p><pre>61127019</pre><hr /><p><strong>Encoded chunks written</strong></p><pre>6</pre><hr /><p><strong>sl_wrap_up_the_backup with status ok</strong></p><pre></pre><hr /><p><strong>Returned technical error details, if any</strong></p><pre></pre><hr /><p><strong>mapi_post URL</strong></p><pre>https://mapi.sitelock.com/v3/connect/</pre><hr /><p><strong>mapi_post_request</strong></p><pre>Array
(
    [pluginVersion] => 100.0.0
    [apiTargetVersion] => 3.0.0
    [token] => 713***
    [requests] => Array
        (
            [id] => 5d2d3ec020931f33b36e8632c06dd1d2-17786931200121
            [action] => s3_queue
            [params] => Array
                (
                    [site_id] => 48126056
                    [queue_id] => 5658578
                    [client_id] => 4598
                    [feature_code] => backup_db
                    [status] => ok
                    [url] => ./.d943d10c313cc7a4b77c5e4b280f8163/everything.sql.zip
                    [zip_file_info] => ./.d943d10c313cc7a4b77c5e4b280f8163/everything.sql.zip-descriptor
                )

        )

)
</pre><hr /><p><strong>curl_getinfo()</strong></p><pre>Array
(
    [url] => https://mapi.sitelock.com/v3/connect/
    [content_type] => text/html; charset=UTF-8
    [http_code] => 200
    [header_size] => 767
    [request_size] => 778
    [filetime] => -1
    [ssl_verify_result] => 20
    [redirect_count] => 0
    [total_time] => 3.156234
    [namelookup_time] => 0.000224
    [connect_time] => 0.031933
    [pretransfer_time] => 0.066795
    [size_upload] => 600
    [size_download] => 742
    [speed_download] => 235
    [speed_upload] => 190
    [download_content_length] => -1
    [upload_content_length] => 600
    [starttransfer_time] => 3.156208
    [redirect_time] => 0
    [redirect_url] => 
    [primary_ip] => 45.60.12.54
    [certinfo] => Array
        (
        )

    [primary_port] => 443
    [local_ip] => 162.254.39.161
    [local_port] => 47068
    [http_version] => 2
    [protocol] => 2
    [ssl_verifyresult] => 0
    [scheme] => https
    [appconnect_time_us] => 66716
    [connect_time_us] => 31933
    [namelookup_time_us] => 224
    [pretransfer_time_us] => 66795
    [redirect_time_us] => 0
    [starttransfer_time_us] => 3156208
    [total_time_us] => 3156234
)
</pre><hr /><p><strong>mapi_response</strong></p><pre><textarea style="width:99%;height:100px;">{"apiVersion":"3.0.1","status":"ok","globalResponse":null,"banner":null,"forceLogout":false,"newToken":null,"now":1778693123,"responses":[{"id":"5d2d3ec020931f33b36e8632c06dd1d2-17786931200121","data":{"s3_status":"ok","queue_id":""},"raw_api_url":"https:\/\/api.sitelock.com\/v1\/backup\/queue","raw_response":{"@attributes":{"version":"1.1","encoding":"UTF-8"},"backup_db":{"message":"OK","status":"ok"}},"raw_request":"<xml>\n  <backup_db site_id=\"48126056\" queue_id=\"5658578\" url=\".\/.d943d10c313cc7a4b77c5e4b280f8163\/everything.sql.zip\" status=\"ok\" zip_file_info=\".\/.d943d10c313cc7a4b77c5e4b280f8163\/everything.sql.zip-descriptor\" backup_db_id=\"4598\"\/>\n<\/xml>","user_agent":"SiteLock Bullet for Backup","status":"ok"}]}</textarea></pre><hr /><p><strong>sl_get_bullet_lock_path:</strong></p><pre>/home/mistgzny/public_html/tmp/7f60836deb74fed9e70e3937f15bc82b.php.lock</pre><hr /><p><strong>sl_unlock_the_bullet: status</strong></p><pre>success</pre><hr /><p><strong>sl_delete_unique_directory - unlink( $descriptor_file[0] );</strong></p><pre>/home/mistgzny/public_html/tmp/.8272ff318afbdf49d1f7350510b97e3a/everything.sql.zip-descriptor</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.8272ff318afbdf49d1f7350510b97e3a/everything.sql.zip.0</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.8272ff318afbdf49d1f7350510b97e3a/everything.sql.zip.1</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.8272ff318afbdf49d1f7350510b97e3a/everything.sql.zip.2</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.8272ff318afbdf49d1f7350510b97e3a/everything.sql.zip.3</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.8272ff318afbdf49d1f7350510b97e3a/everything.sql.zip.4</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.8272ff318afbdf49d1f7350510b97e3a/everything.sql.zip.5</pre><hr /><p><strong>sl_delete_unique_directory - rmdir( $path )</strong></p><pre>/home/mistgzny/public_html/tmp/.8272ff318afbdf49d1f7350510b97e3a</pre><hr /><p><strong>Bullet run time, seconds.</strong></p><pre>8.56</pre><hr />