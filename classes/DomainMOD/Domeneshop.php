<?php

namespace DomainMOD;

class Domeneshop
{
    public $format;
    public $log;

    public function __construct()
    {
        $this->format = new Format();
        $this->log = new Log('class.domeneshop');
    }

    public function getApiUrl($domain, $command)
    {
        $base_url = 'https://api.domeneshop.no/v0/';        
        if ($command == 'domainlist') {
            return $base_url . 'domains';
        } elseif ($command == 'info') {
            return $base_url . 'domains?domain=' . $domain;
        } else {
            return _('Unable to build API URL');
        }
    }

    public function apiCall($api_key, $api_secret, $full_url)
    {
        $handle = curl_init($full_url);
        $headers = array(
            'Authorization: Basic ' . base64_encode($api_key . ':' . $api_secret),
            'Accept: application/json');

        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($handle);
        curl_close($handle);
        return $result;
    }

    public function getDomainList($api_key, $api_secret)
    {
        $domain_list = array();
        $domain_count = 0;

        $log_extra = array('API Key' => $this->format->obfusc($api_key), 'API Secret' => $this->format->obfusc($api_secret));

        $api_url = $this->getApiUrl('', 'domainlist');

        $api_results = $this->apiCall($api_key, $api_secret, $api_url);
        $array_results = json_decode($api_results, true);
 
        if (isset($array_results[0]['domain'])) {
            foreach ($array_results as $domain) {
                $domain_list[] = $domain['domain'];
                $domain_count++;
            }
        } else {
            $log_message = 'Unable to get domain list';
            $log_extra = array('API Key' => $this->format->obfusc($api_key), 'API Secret' => $this->format->obfusc($api_secret));
            $this->log->error($log_message, $log_extra);
        }

        return array($domain_count, $domain_list);
    }

    public function getFullInfo($api_key, $api_secret, $domain)
    {
        $expiration_date = '';
        $dns_servers = array();
        $autorenewal_status = '';
        $domain_status = '';
        $privacy_status = '0';

        $api_url = $this->getApiUrl($domain, 'info');
        $api_results = $this->apiCall($api_key, $api_secret, $api_url);
        $array_results = json_decode($api_results, true);

        if (isset($array_results[0]['domain'])) {
            foreach ($array_results as $fdom) {
                if($fdom['domain'] == $domain) {
                    $expiration_date = (string) $fdom['expiry_date'];
                    $dns_servers = $fdom['nameservers'];
                    $autorenewal_status = (string) $fdom['renew'];
                    $domain_status = (string) $fdom['status'];
                    print("returning values: " . $domain_status . ":" . $expiration_date . ":" . "DNS" . ":" . $privacy_status . ":" . $autorenewal_status . "\n");
                    return array($domain_status, $expiration_date, $dns_servers, $privacy_status, $autorenewal_status);
                }
            }
        } else {

            $log_message = 'Unable to get full domain info';
            $log_extra = array('API Key' => $this->format->obfusc($api_key), 'API Secret' => $this->format->obfusc($api_secret), 'Domain' => $domain);
            $this->log->error($log_message, $log_extra);
        }
    }

}
