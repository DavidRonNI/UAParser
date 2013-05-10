<?php

namespace UAParser;

use UAParser\Result;
use UAParser\Result\ResultFactory;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Benjamin Laugueux <benjamin@yzalis.com>
 */
class UAParser implements UAParserInterface
{
    /**
     * @var array
     */
    private $regexes = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->regexes = Yaml::parse(__DIR__.'/../../regexes.yml');
    }

    /**
     * {@inheritDoc}
     */
    public function parse($userAgent, $referer = null)
    {
        $data = array(
            'browser'          => $this->parseBrowser($userAgent),
            'device'           => $this->parseDevice($userAgent),
            'operating_system' => $this->parseOperatingSystem($userAgent),
            'email_client'     => $this->parseEmailClient($userAgent, $referer),
        );

        return $this->prepareResult($data);
    }

    /**
     * Parse the user agent an extract the browser informations
     * 
     * @param string $userAgent the user agent string
     * 
     * @return array
     */
    protected function parseBrowser($userAgent)
    {
        $result = array(
            'family' => 'Other',
            'major'  => null,
            'minor'  => null,
            'patch'  => null,
        );

        foreach ($this->regexes['browser_parsers'] as $browserRegexe) {
            if (preg_match('/'.str_replace('/','\/',str_replace('\/','/', $browserRegexe['regex'])).'/i', $userAgent, $browserMatches)) {
                if (!isset($browserMatches[1])) { $browserMatches[1] = 'Other'; }
                if (!isset($browserMatches[2])) { $browserMatches[2] = null; }
                if (!isset($browserMatches[3])) { $browserMatches[3] = null; }
                if (!isset($browserMatches[4])) { $browserMatches[4] = null; }
                
                $result['family'] = isset($browserRegexe['family_replacement']) ? str_replace('$1', $browserMatches[1], $browserRegexe['family_replacement']) : $browserMatches[1];
                $result['major']  = isset($browserRegexe['major_replacement']) ? $browserRegexe['major_replacement'] : $browserMatches[2];
                $result['minor']  = isset($browserRegexe['minor_replacement']) ? $browserRegexe['minor_replacement'] : $browserMatches[3];
                $result['patch']  = isset($browserRegexe['patch_replacement']) ? $browserRegexe['patch_replacement'] : $browserMatches[4];

                goto rederingEngine;
            }
        }

        rederingEngine:

        foreach ($this->regexes['rendering_engine_parsers'] as $renderingEngineRegex) {
            if (preg_match('/'.str_replace('/','\/',str_replace('\/','/', $renderingEngineRegex['regex'])).'/i', $userAgent, $renderingEngineMatches)) {
                if (!isset($renderingEngineMatches[1])) { $renderingEngineMatches[1] = 'Other'; }
                
                $result['rendering_engine'] = isset($renderingEngineRegex['rendering_engine_replacement']) ? str_replace('$1', $renderingEngineMatches[1], $renderingEngineRegex['rendering_engine_replacement']) : $renderingEngineMatches[1];

                return $result;
            }
        }

        return $result;
    }

    /**
     * Parse the user agent an extract the operating system informations
     * 
     * @param string $userAgent the user agent string
     * 
     * @return array
     */
    protected function parseOperatingsystem($userAgent)
    {
        $result = array(
            'family' => 'Other',
            'major'  => null,
            'minor'  => null,
            'patch'  => null,
        );

        foreach ($this->regexes['operating_system_parsers'] as $operatingSystemRegex) {
            if (preg_match('/'.str_replace('/','\/',str_replace('\/','/', $operatingSystemRegex['regex'])).'/i', $userAgent, $matches)) {
                if (!isset($matches[1])) { $matches[1] = 'Other'; }
                if (!isset($matches[2])) { $matches[2] = null; }
                if (!isset($matches[3])) { $matches[3] = null; }
                if (!isset($matches[4])) { $matches[4] = null; }
                
                $result['family'] = isset($operatingSystemRegex['family_replacement']) ? str_replace('$1', $matches[1], $operatingSystemRegex['family_replacement']) : $matches[1];
                $result['major']  = isset($operatingSystemRegex['major_replacement']) ? $operatingSystemRegex['major_replacement'] : $matches[2];
                $result['minor']  = isset($operatingSystemRegex['minor_replacement']) ? $operatingSystemRegex['minor_replacement'] : $matches[3];
                $result['patch']  = isset($operatingSystemRegex['patch_replacement']) ? $operatingSystemRegex['patch_replacement'] : $matches[4];

                return $result;
            }
        }

        return $result;
    }

    /**
     * Parse the user agent an extract the device informations
     * 
     * @param string $userAgent the user agent string
     * 
     * @return array
     */
    protected function parseDevice($userAgent)
    {
        $result = array(
            'constructor' => 'Other',
            'model'       => null,
            'type'        => null,
        );

        foreach ($this->regexes['device_parsers'] as $deviceRegex) {
            if (preg_match('/'.str_replace('/','\/',str_replace('\/','/', $deviceRegex['regex'])).'/i', $userAgent, $matches)) {
                if (!isset($matches[1])) { $matches[1] = 'Other'; }
                if (!isset($matches[2])) { $matches[2] = null; }
                if (!isset($matches[3])) { $matches[3] = null; }
                
                $result['constructor'] = isset($deviceRegex['constructor_replacement']) ? str_replace('$1', $matches[1], $deviceRegex['constructor_replacement']) : $matches[1];
                $result['model']       = isset($deviceRegex['model_replacement']) ? str_replace('$1', $matches[1], $deviceRegex['model_replacement']) : $matches[2];
                $result['type']        = isset($deviceRegex['type_replacement']) ? $deviceRegex['type_replacement'] : $matches[3];

                return $result;
            }
        }

        return $result;
    }

    /**
     * Parse the user agent and optionnaly the refere an extract the email client informations
     * 
     * @param string $userAgent the user agent string
     * @param string|null $referer A request referer to parse.
     * 
     * @return array
     */
    protected function parseEmailClient($userAgent, $referer = null)
    {
        $result = array(
            'family' => 'Other',
            'major'  => null,
            'minor'  => null,
            'patch'  => null,
        );

        foreach ($this->regexes['email_client_parsers'] as $emailClientRegexe) {
            if (preg_match('/'.str_replace('/','\/',str_replace('\/','/', $emailClientRegexe['regex'])).'/i', $userAgent, $emailClientMatches)) {
                if (!isset($emailClientMatches[1])) { $emailClientMatches[1] = 'Other'; }
                if (!isset($emailClientMatches[2])) { $emailClientMatches[2] = null; }
                if (!isset($emailClientMatches[3])) { $emailClientMatches[3] = null; }
                if (!isset($emailClientMatches[4])) { $emailClientMatches[4] = null; }
                if (!isset($emailClientMatches[5])) { $emailClientMatches[5] = null; }
                
                $result['family'] = isset($emailClientRegexe['family_replacement']) ? str_replace('$1', $emailClientMatches[1], $emailClientRegexe['family_replacement']) : $emailClientMatches[1];
                $result['major']  = isset($emailClientRegexe['major_replacement']) ? $emailClientRegexe['major_replacement'] : $emailClientMatches[2];
                $result['minor']  = isset($emailClientRegexe['minor_replacement']) ? $emailClientRegexe['minor_replacement'] : $emailClientMatches[3];
                $result['patch']  = isset($emailClientRegexe['patch_replacement']) ? $emailClientRegexe['patch_replacement'] : $emailClientMatches[4];
                $result['type']   = isset($emailClientRegexe['type_replacement']) ? $emailClientRegexe['type_replacement'] : $emailClientMatches[5];

                goto referer;
            }
        }

        referer:

        if ($result['family'] == 'Other' && null !== $referer) {
            foreach ($this->regexes['email_client_parsers'] as $emailClientRegexe) {
                if (preg_match('/'.str_replace('/','\/',str_replace('\/','/', $emailClientRegexe['regex'])).'/i', $referer, $emailClientRefererMatches)) {
                    if (!isset($emailClientRefererMatches[1])) { $emailClientRefererMatches[1] = 'Other'; }
                    if (!isset($emailClientRefererMatches[2])) { $emailClientRefererMatches[2] = null; }
                    
                    $result['family'] = isset($emailClientRegexe['family_replacement']) ? str_replace('$1', $emailClientRefererMatches[1], $emailClientRegexe['family_replacement']) : $emailClientRefererMatches[1];
                    $result['type']   = isset($emailClientRegexe['type_replacement']) ? $emailClientRegexe['type_replacement'] : $emailClientRefererMatches[2];

                    return $result;
                }
            }
        }

        return $result;
    }

    /**
     * Prepare the result set
     * 
     * @param array $data An array of data.
     *
     * @return ResultInterface
     */
    protected function prepareResult(array $data = array())
    {
        $resultFactory = new ResultFactory();

        return $resultFactory->createFromArray($data);
    }
}