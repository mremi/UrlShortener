<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) zacksleo <zacksleo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Command;

use Mremi\UrlShortener\Model\Link;
use Mremi\UrlShortener\Provider\Wechat\WechatProvider;
use Mremi\UrlShortener\Provider\Wechat\OAuthClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shortens the long given URL using the Wechat API.
 *
 * @author zacksleo <zacksleo@gmail.com>
 */
class WechatShortenCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('wechat:shorten')
            ->setDescription('Shortens the long given URL using the Wechat API')

            ->addArgument('appid', InputArgument::REQUIRED, 'A valid Wechat appid')
            ->addArgument('appsecret', InputArgument::REQUIRED, 'A valid Wechat appsecret')
            ->addArgument('long-url', InputArgument::REQUIRED, 'The long URL to shorten')

            ->addOption('options', null, InputOption::VALUE_REQUIRED, 'An array of options used by request');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $link = new Link();
        $link->setLongUrl($input->getArgument('long-url'));

        $provider = new WechatProvider(
            new OAuthClient($input->getArgument('appid'), $input->getArgument('appsecret'))
        );

        try {
            $provider->shorten($link);

            $output->writeln(sprintf('<info>Success:</info> %s', $link->getShortUrl()));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Failure:</error> %s', $e->getMessage()));
        }
    }
}
