<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;
use mysql_xdevapi\Exception;

/**
 * @license http://opensource.org/licenses/MIT MIT
 *
 * This is the engine for the german Volksbank
 */
class Vbk extends Spk
{
    /**
     * returns the name of the bank.
     *
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'Vbk';
    }

    protected function parseTransactionAccount()
    {
        $input = $this->sanitizeOutput($this->sanitizeInput($this->getCurrentTransactionData()));
        $input = str_replace([' ', PHP_EOL], '', $input);
        $results = [];

        if (preg_match('/IBAN:([A-Z]{2}[0-9]+)/', $input, $results) && !empty($results[1])) {

            if (strlen($results[1]) > 50) {
                \Neos\Flow\var_dump($input, __METHOD__ . ':' . __LINE__);
                throw new \Exception('TransactionAccount could not be parsed, account results in: ' . $results[1], 1570712418);
            }

            return $this->sanitizeOutput($results[1]);
        }

        return '';
    }

    protected function parseTransactionAccountName()
    {
        $input = $this->sanitizeInput($this->getCurrentTransactionData());
        $results = [];
        if (preg_match('/:86:.*\?32(.*)/', $input, $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeOutput($results[1]);
        }

        return '';
    }

    protected function parseTransactionDescription()
    {
        $input = $this->sanitizeInput($this->getCurrentTransactionData());
        $results = [];

        if (preg_match('/:86:.*\?20(.*)(?:EREF:|IBAN:)\?.*/s', $input, $results) && !empty($results[1])) {
            $result = $this->sanitizeOutput($results[1]);
            return $result;
        }

        if (preg_match('/:86:(.*)/', $input, $results) && !empty($results[1])) {
            $result = $this->sanitizeOutput($results[1]);
            return $result;
        }

        \Neos\Flow\var_dump($input, __METHOD__ . ':' . __LINE__);

        return '';
    }

    /**
     * @param string $input
     * @return string
     */
    private function sanitizeInput(string $input): string
    {
        $output = str_replace(PHP_EOL, '', $this->getCurrentTransactionData());
        $output = str_replace('IB AN:?', 'IBAN:?', $output);
        return $output;
    }

    /**
     * @param string $input
     * @return string
     */
    private function sanitizeOutput(string $input): string
    {
        $output = str_replace(['?21', '?22', '?23', '?24', '?25', '?26', '?27', '?28', '?29', '?30', '?31', '?32', '?33', '?60', '?61', '?62'], ' ', $input);
        $output = trim($output);
        return $output;
    }
}


