<div style='background-color:#AFF'><h3>Encryption</h3><p><strong>PHP Version</strong></p><pre>8.1.33</pre><hr /><p><strong>Cryptor</strong></p><pre>OpenSSL</pre><hr /><p><strong>Default Cipher</strong></p><pre>aes-256-cbc</pre><hr /><p><strong>mb_internal_encoding</strong></p><pre>UTF-8</pre><hr /><div style='background-color:#AAA'><h3>IP Validation</h3><p><strong>$headers from sl_get_ip()</strong></p><pre>Array
(
    [Connection] => TE
    [Host] => mistermediasolution.com
    [User-Agent] => SiteLock (Module: SmartDB; Source: https://www.sitelock.com/; Version: 1.0)
    [x-forwarded-proto] => https
    [x-https] => on
    [X-Forwarded-For] => 184.154.76.40
)
</pre><hr /><p><strong>IP Check started in</strong></p><pre>/home/mistgzny/public_html/tmp/14c9131556948c80ac875ec78bc0dd7f.php</pre><hr /><p><strong>IP Check started at</strong></p><pre>2025-11-21T11:18:56-05:00</pre><hr /><p><strong>The following IPs will be tested</strong></p><pre>Array
(
    [0] => 184.154.76.40
)
</pre><hr /><p><strong>mapi_post URL</strong></p><pre>https://mapi.sitelock.com/v3/connect/</pre><hr /><p><strong>mapi_post_request</strong></p><pre>Array
(
    [pluginVersion] => 100.0.0
    [apiTargetVersion] => 3.0.0
    [token] => 78e***
    [requests] => Array
        (
            [id] => 254220ff2f725e577ce5543bf15805f4-17637419360826
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
    [header_size] => 763
    [request_size] => 510
    [filetime] => -1
    [ssl_verify_result] => 20
    [redirect_count] => 0
    [total_time] => 0.364215
    [namelookup_time] => 0.000143
    [connect_time] => 0.011798
    [pretransfer_time] => 0.027481
    [size_upload] => 324
    [size_download] => 526
    [speed_download] => 1444
    [speed_upload] => 889
    [download_content_length] => -1
    [upload_content_length] => 324
    [starttransfer_time] => 0.364186
    [redirect_time] => 0
    [redirect_url] => 
    [primary_ip] => 45.60.12.54
    [certinfo] => Array
        (
        )

    [primary_port] => 443
    [local_ip] => 162.254.39.161
    [local_port] => 54880
    [http_version] => 2
    [protocol] => 2
    [ssl_verifyresult] => 0
    [scheme] => https
    [appconnect_time_us] => 27435
    [connect_time_us] => 11798
    [namelookup_time_us] => 143
    [pretransfer_time_us] => 27481
    [redirect_time_us] => 0
    [starttransfer_time_us] => 364186
    [total_time_us] => 364215
)
</pre><hr /><p><strong>mapi_response</strong></p><pre><textarea style="width:99%;height:100px;">{"apiVersion":"3.0.1","status":"ok","globalResponse":null,"banner":null,"forceLogout":false,"newToken":null,"now":1763741936,"responses":[{"id":"254220ff2f725e577ce5543bf15805f4-17637419360826","data":{"ip_address":"184.154.76.40","valid":true},"raw_api_url":"https:\/\/api.sitelock.com\/v1\/dbscan\/checkip","raw_response":{"@attributes":{"version":"1.1","encoding":"UTF-8"},"checkIP":{"status":"1"}},"raw_request":{"site_id":"48126056","ip":"184.154.76.40"},"user_agent":"SiteLock Bullet for DBScan (other)","status":"ok"}]}</textarea></pre><hr /><div style='background-color:#AFA'><h3>GrabAndZip</h3><p><strong>_POST</strong></p><pre>Array
(
)
</pre><hr /><p><strong>_GET (raw)</strong></p><pre>cmd=db_creds_ready&enc_db_creds=sh3Zta15wcurT2MuVkPfV6ger0XDCnttlCWWTshtNZzMXFRtdNKwjj8pVyfUMcJQSdcPL69u%2BJZSj8GzoN6R36SlAh9Svr2sKJ2q%2FNcRsfQIKX0xvrvyfsN064LhCaGhO%2BRDjUPmopeY%2BKzoTaxOCexUSNfDsQRpO2Hob0GdHVZ3u3M4%2FZO5XVAqNQnl13mGDod%2FsaZli3Mnu%2BEozWmlz8poiKX6bNVtER4Fmto%2BGu8%3D&smart_single_download_id=5549466</pre><hr /><p><strong>Detected memory_limit</strong></p><pre>10240M</pre><hr /><p><strong>Chunk Size</strong></p><pre>10485760</pre><hr /><p><strong>mapi_post URL</strong></p><pre>https://mapi.sitelock.com/v3/connect/</pre><hr /><p><strong>mapi_post_request</strong></p><pre>Array
(
    [pluginVersion] => 100.0.0
    [apiTargetVersion] => 3.0.0
    [token] => 78e***
    [requests] => Array
        (
            [id] => 58c87cb21015f816c2943224a990b3a4-17637419364471
            [action] => s3_get_enc_info
            [params] => Array
                (
                    [site_id] => 48126056
                    [queue_id] => 5549466
                )

        )

)
</pre><hr /><p><strong>curl_getinfo()</strong></p><pre>Array
(
    [url] => https://mapi.sitelock.com/v3/connect/
    [content_type] => text/html; charset=UTF-8
    [http_code] => 200
    [header_size] => 763
    [request_size] => 514
    [filetime] => -1
    [ssl_verify_result] => 20
    [redirect_count] => 0
    [total_time] => 0.327245
    [namelookup_time] => 0.000138
    [connect_time] => 0.0123
    [pretransfer_time] => 0.026147
    [size_upload] => 328
    [size_download] => 835
    [speed_download] => 2551
    [speed_upload] => 1002
    [download_content_length] => -1
    [upload_content_length] => 328
    [starttransfer_time] => 0.327214
    [redirect_time] => 0
    [redirect_url] => 
    [primary_ip] => 45.60.12.54
    [certinfo] => Array
        (
        )

    [primary_port] => 443
    [local_ip] => 162.254.39.161
    [local_port] => 54884
    [http_version] => 2
    [protocol] => 2
    [ssl_verifyresult] => 0
    [scheme] => https
    [appconnect_time_us] => 26088
    [connect_time_us] => 12300
    [namelookup_time_us] => 138
    [pretransfer_time_us] => 26147
    [redirect_time_us] => 0
    [starttransfer_time_us] => 327214
    [total_time_us] => 327245
)
</pre><hr /><p><strong>Received encryption details</strong></p><pre>Array
(
    [cipher] => aes-256-cbc
    [key] => /RS***
    [iv] => xQe***
)
</pre><hr /><p><strong>_TABLES</strong></p><pre>Array
(
    [app_orders] => Array
        (
            [last_id] => 0
        )

    [app_order_items] => Array
        (
            [last_id] => 0
        )

    [app_auto_payouts] => Array
        (
            [last_id] => 0
        )

    [app_purchase] => Array
        (
            [last_id] => 0
        )

    [app_suppliers] => Array
        (
            [last_id] => 0
        )

    [app_systemlogs] => Array
        (
            [last_id] => 0
        )

    [app_packages] => Array
        (
            [last_id] => 0
        )

    [app_packing_sizes] => Array
        (
            [last_id] => 0
        )

    [app_settings] => Array
        (
            [last_id] => 0
        )

    [app_permissions] => Array
        (
            [last_id] => 0
        )

    [app_purchase_orders] => Array
        (
            [last_id] => 0
        )

    [app_sellprices_name] => Array
        (
            [last_id] => 0
        )

    [app_payments] => Array
        (
            [last_id] => 0
        )

    [app_messages] => Array
        (
            [last_id] => 0
        )

    [app_payouts] => Array
        (
            [last_id] => 0
        )

    [app_stocks] => Array
        (
            [last_id] => 0
        )

    [app_items] => Array
        (
            [last_id] => 0
        )

    [app_accounts] => Array
        (
            [last_id] => 0
        )

    [app_roles] => Array
        (
            [last_id] => 0
        )

    [csv_files] => Array
        (
            [last_id] => 0
        )

    [app_invoices] => Array
        (
            [last_id] => 0
        )

    [app_admins] => Array
        (
            [last_id] => 0
        )

    [app_invoices_details] => Array
        (
            [last_id] => 0
        )

)
</pre><hr /><p><strong>_QUOTA</strong></p><pre>0</pre><hr /><p><strong>_SCANDATE</strong></p><pre>1763741936</pre><hr /><p><strong>sl_get_bullet_lock_path:</strong></p><pre>/home/mistgzny/public_html/tmp/14c9131556948c80ac875ec78bc0dd7f.php.lock</pre><hr /><p><strong>sl_is_bullet_locked check:</strong></p><pre>not locked (no lock file)</pre><hr /><p><strong>sl_get_bullet_lock_path:</strong></p><pre>/home/mistgzny/public_html/tmp/14c9131556948c80ac875ec78bc0dd7f.php.lock</pre><hr /><p><strong>sl_lock_the_bullet: bytes written</strong></p><pre>10</pre><hr /><p><strong>Starting MySQLi constructor</strong></p><pre></pre><hr /><p><strong>MySQL Version Detected</strong></p><pre>11.4.8-MariaDB-cll-lve</pre><hr /><p><strong>Tables Summary</strong></p><pre></pre><hr /><pre>app_orders: 2500 records pulled; 2500 running total.</pre><hr /><pre>app_order_items: 2500 records pulled; 5000 running total.</pre><hr /><pre>app_auto_payouts: 2500 records pulled; 7500 running total.</pre><hr /><pre>app_purchase: 314 records pulled; 7814 running total.</pre><hr /><pre>app_suppliers: 6 records pulled; 7820 running total.</pre><hr /><pre>app_systemlogs: 2500 records pulled; 10320 running total.</pre><hr /><pre>app_packages: 666 records pulled; 10986 running total.</pre><hr /><pre>app_packing_sizes: 3 records pulled; 10989 running total.</pre><hr /><pre>app_settings: 6 records pulled; 10995 running total.</pre><hr /><pre>app_permissions: 35 records pulled; 11030 running total.</pre><hr /><pre>app_purchase_orders: 0 records pulled; 11030 running total.</pre><hr /><pre>app_sellprices_name: 14 records pulled; 11044 running total.</pre><hr /><pre>app_payments: 1222 records pulled; 12266 running total.</pre><hr /><pre>app_messages: 2383 records pulled; 14649 running total.</pre><hr /><pre>app_payouts: 401 records pulled; 15050 running total.</pre><hr /><pre>app_stocks: 2371 records pulled; 17421 running total.</pre><hr /><pre>app_items: 1291 records pulled; 18712 running total.</pre><hr /><pre>app_accounts: 96 records pulled; 18808 running total.</pre><hr /><pre>app_roles: 24 records pulled; 18832 running total.</pre><hr /><pre>csv_files: 1 records pulled; 18833 running total.</pre><hr /><pre>app_invoices: 0 records pulled; 18833 running total.</pre><hr /><pre>app_admins: 23 records pulled; 18856 running total.</pre><hr /><pre>app_invoices_details: 2 records pulled; 18858 running total.</pre><hr /><p><strong>Total time to query and write tables</strong></p><pre>0.33867597579956</pre><hr /><p><strong>sl_archive_files</strong></p><pre>start</pre><hr /><p><strong>sl_archive_files 5</strong></p><pre>Send to CLI0</pre><hr /><p><strong>archived file</strong></p><pre>/home/mistgzny/public_html/tmp/.209550f8e530dedb9876b4a602c95d5e/0115413001763741937.zip</pre><hr /><p><strong>Original ZIP size</strong></p><pre>3643109</pre><hr /><p><strong>bytes written to /home/mistgzny/public_html/tmp/.209550f8e530dedb9876b4a602c95d5e/0115413001763741937.zip.0</strong></p><pre>3643120</pre><hr /><p><strong>sl_wrap_up_the_scan with status ok</strong></p><pre></pre><hr /><p><strong>Returned technical error details, if any</strong></p><pre></pre><hr /><p><strong>mapi_post URL</strong></p><pre>https://mapi.sitelock.com/v3/connect/</pre><hr /><p><strong>mapi_post_request</strong></p><pre>Array
(
    [pluginVersion] => 100.0.0
    [apiTargetVersion] => 3.0.0
    [token] => 78e***
    [requests] => Array
        (
            [id] => b1c19d1eceadb00ffd25bbdfee6126d8-17637419373288
            [action] => s3_queue
            [params] => Array
                (
                    [site_id] => 48126056
                    [queue_id] => 5549466
                    [client_id] => 15895
                    [feature_code] => db_scan
                    [status] => ok
                    [url] => .209550f8e530dedb9876b4a602c95d5e/0115413001763741937.zip
                    [zip_file_info] => .209550f8e530dedb9876b4a602c95d5e/0115413001763741937.zip-descriptor
                    [findings] => Array
                        (
                            [php] => 8.1.33
                            [mysql] => 11.4.8-MariaDB-cll-lve
                        )

                )

        )

)
</pre><hr /><p><strong>curl_getinfo()</strong></p><pre>Array
(
    [url] => https://mapi.sitelock.com/v3/connect/
    [content_type] => text/html; charset=UTF-8
    [http_code] => 200
    [header_size] => 761
    [request_size] => 870
    [filetime] => -1
    [ssl_verify_result] => 20
    [redirect_count] => 0
    [total_time] => 0.348716
    [namelookup_time] => 0.000152
    [connect_time] => 0.012136
    [pretransfer_time] => 0.025038
    [size_upload] => 684
    [size_download] => 874
    [speed_download] => 2506
    [speed_upload] => 1961
    [download_content_length] => -1
    [upload_content_length] => 684
    [starttransfer_time] => 0.348686
    [redirect_time] => 0
    [redirect_url] => 
    [primary_ip] => 45.60.12.54
    [certinfo] => Array
        (
        )

    [primary_port] => 443
    [local_ip] => 162.254.39.161
    [local_port] => 54892
    [http_version] => 2
    [protocol] => 2
    [ssl_verifyresult] => 0
    [scheme] => https
    [appconnect_time_us] => 24983
    [connect_time_us] => 12136
    [namelookup_time_us] => 152
    [pretransfer_time_us] => 25038
    [redirect_time_us] => 0
    [starttransfer_time_us] => 348686
    [total_time_us] => 348716
)
</pre><hr /><p><strong>mapi_response</strong></p><pre><textarea style="width:99%;height:100px;">{"apiVersion":"3.0.1","status":"ok","globalResponse":null,"banner":null,"forceLogout":false,"newToken":null,"now":1763741937,"responses":[{"id":"b1c19d1eceadb00ffd25bbdfee6126d8-17637419373288","data":{"s3_status":"approved","queue_id":"5549466"},"raw_api_url":"https:\/\/api.sitelock.com\/v1\/dbscan\/queue","raw_response":{"@attributes":{"version":"1.1","encoding":"UTF-8"},"dbscan":{"queue_id":"5549466","status":"approved"}},"raw_request":"<xml>\n  <dbscan site_id=\"48126056\" queue_id=\"5549466\" url=\".209550f8e530dedb9876b4a602c95d5e\/0115413001763741937.zip\" status=\"ok\" zip_file_info=\".209550f8e530dedb9876b4a602c95d5e\/0115413001763741937.zip-descriptor\" client_id=\"15895\" findings=\"{&quot;php&quot;:&quot;8.1.33&quot;,&quot;mysql&quot;:&quot;11.4.8-MariaDB-cll-lve&quot;}\"\/>\n<\/xml>","user_agent":"SiteLock Bullet for DBScan (other)","status":"ok"}]}</textarea></pre><hr /><p><strong>sl_get_bullet_lock_path:</strong></p><pre>/home/mistgzny/public_html/tmp/14c9131556948c80ac875ec78bc0dd7f.php.lock</pre><hr /><p><strong>sl_is_bullet_locked check time:</strong></p><pre>current: 1763741937, locked at: 1763741936, diff: 1, still locked: Yes</pre><hr /><p><strong>sl_get_bullet_lock_path:</strong></p><pre>/home/mistgzny/public_html/tmp/14c9131556948c80ac875ec78bc0dd7f.php.lock</pre><hr /><p><strong>sl_unlock_the_bullet: status</strong></p><pre>success</pre><hr /><p><strong>sl_delete_unique_directory - unlink( $descriptor_file[0] );</strong></p><pre>/home/mistgzny/public_html/tmp/.ab0d481801a51892b305328e5cab7900/everything.sql.zip-descriptor</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.ab0d481801a51892b305328e5cab7900/everything.sql.zip.0</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.ab0d481801a51892b305328e5cab7900/everything.sql.zip.1</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.ab0d481801a51892b305328e5cab7900/everything.sql.zip.2</pre><hr /><p><strong>sl_delete_unique_directory - file chunk</strong></p><pre>/home/mistgzny/public_html/tmp/.ab0d481801a51892b305328e5cab7900/everything.sql.zip.3</pre><hr /><p><strong>sl_delete_unique_directory - rmdir( $path )</strong></p><pre>/home/mistgzny/public_html/tmp/.ab0d481801a51892b305328e5cab7900</pre><hr /><p><strong>Bullet run time, seconds.</strong></p><pre>1.6</pre><hr />