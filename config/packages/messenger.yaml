# see https://github.com/symfony/recipes/blob/master/symfony/messenger/4.3/config/packages/messenger.yaml
framework:
    messenger:
        # Uncomment this (and the failed transport below) to send failed messages to this transport for later handling.
        # failure_transport: failed

        transports:
            # https://symfony.com/doc/current/messenger.html#transport-configuration
            # async: '%env(MESSENGER_TRANSPORT_DSN)%'
            # failed: 'doctrine://default?queue_name=failed'
            # sync: 'sync://'

        routing:
            # Route your messages to the transports
            # 'App\Message\YourMessage': async

        default_bus: command_bus
        buses:
            command_bus: ~
            event_bus:
                default_middleware: allow_no_handlers

services:
    msgphp.messenger.command_bus: '@command_bus'
    msgphp.messenger.event_bus: '@event_bus'
