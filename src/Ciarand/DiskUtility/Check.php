<?php
namespace Ciarand\DiskUtility;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\TableHelper;

class Check extends Command {
    protected function configure() {
        $this
            ->setName('disk_utility:check')
            ->setDescription('Check your root drive\'s current usage stats.')
        ;
    }

    protected function decodeSize( $bytes ) {
        $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        for ($i = 0; $bytes >= 1024 && $i < (count($types) -1); $bytes /= 1024, $i++);
        return(round($bytes, 2) . " " . $types[$i]);
    }

    protected function percent($num_amount, $num_total) {
        $count1 = $num_amount / $num_total;
        $count2 = $count1 * 100;
        $count = number_format($count2, 2);
        return $count;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        //Available foreground and background colors are: black, red, green, yellow, blue, magenta, cyan and white.
        $dangerStyle = new OutputFormatterStyle('red', null, array('bold',));
        $output->getFormatter()->setStyle('danger', $dangerStyle);


        $machine = array();
        $human   = array();
        $percent = array();

        $machine['free']  = disk_free_space('/');
        $machine['total'] = disk_total_space('/');
        $machine['used']  = ($machine['total'] - $machine['free']);

        foreach ($machine as $label => $stat) {
            $human[$label] = $this->decodeSize($stat);
            $rawPercent = $this->percent($stat, $machine['total']);
            if ($rawPercent <= 10) {
                $percent[$label] = $dangerStyle->apply($rawPercent);
                $percent[$label] = $rawPercent;
            } else {
                $percent[$label] = $rawPercent;
            }
            $percent[$label] .= '%';
        }

        $table = $this->getHelperSet()->get('table');
        $table
            ->setHeaders(
                array(
                    'Disk Size',
                    '',
                )
            )
            ->setRows(
                array(
                    array('Free', $human['free'], $percent['free']),
                    array('Used', $human['used'], $percent['used']),
                    array('Total', $human['total'], $percent['total']),
                )
            )
        ;
        $table->render($output);
    }
}
