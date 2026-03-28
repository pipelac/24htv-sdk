<?php

namespace TwentyFourTv;

use TwentyFourTv\Callback\CallbackHandler;
use TwentyFourTv\Contract\ConfigInterface;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Contract\LoggerInterface;
use TwentyFourTv\Contract\Service\AuthServiceInterface;
use TwentyFourTv\Contract\Service\BalanceServiceInterface;
use TwentyFourTv\Contract\Service\ChannelServiceInterface;
use TwentyFourTv\Contract\Service\ContractServiceInterface;
use TwentyFourTv\Contract\Service\DeviceServiceInterface;
use TwentyFourTv\Contract\Service\MessageServiceInterface;
use TwentyFourTv\Contract\Service\PacketServiceInterface;
use TwentyFourTv\Contract\Service\PromoServiceInterface;
use TwentyFourTv\Contract\Service\SubscriptionServiceInterface;
use TwentyFourTv\Contract\Service\TagServiceInterface;
use TwentyFourTv\Contract\Service\UserServiceInterface;
use TwentyFourTv\Http\ServerRequest;
use TwentyFourTv\Model\User;
use TwentyFourTv\Service\AuthService;
use TwentyFourTv\Service\BalanceService;
use TwentyFourTv\Service\ChannelService;
use TwentyFourTv\Service\ContractService;
use TwentyFourTv\Service\DeviceService;
use TwentyFourTv\Service\MessageService;
use TwentyFourTv\Service\PacketService;
use TwentyFourTv\Service\PromoService;
use TwentyFourTv\Service\SubscriptionService;
use TwentyFourTv\Service\TagService;
use TwentyFourTv\Service\UserService;

/**
 * Фасад SDK для работы с платформой 24часаТВ
 *
 * Предоставляет доступ ко всем сервисам через ленивую инициализацию.
 * Каждый сервис создаётся при первом обращении и кэшируется.
 *
 * <code>
 * $client = ClientFactory::create('/path/to/24htv.ini', $logger);
 *
 * // Работа с пользователями
 * $user = $client->users()->register([...]);
 *
 * // Подмена реализации сервиса
 * $client->registerService(UserServiceInterface::class, function() {
 *     return new MyCustomUserService($httpClient, $logger);
 * });
 * </code>
 *
 * @since 1.0.0
 */
class Client
{
    /** @var HttpClientInterface */
    private $httpClient;

    /** @var ConfigInterface */
    private $config;

    /** @var LoggerInterface|null */
    private $logger;

    /** @var array Закэшированные инстансы сервисов */
    private $services = [];

    /** @var array Кастомные фабрики сервисов */
    private $customFactories = [];

    /** @var CallbackHandler|null */
    private $callbackHandler;

    /**
     * @param HttpClientInterface $httpClient HTTP-транспорт
     * @param ConfigInterface     $config     Конфигурация SDK
     * @param LoggerInterface|null $logger    Логгер (опционально)
     */
    public function __construct(
        HttpClientInterface $httpClient,
        ConfigInterface $config,
        LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->logger = $logger;
    }

    // ==========================================
    // СЕРВИСЫ (Lazy Loading)
    // ==========================================

    /**
     * Сервис управления пользователями
     *
     * @return UserServiceInterface
     */
    public function users()
    {
        return $this->resolveService(
            UserServiceInterface::class,
            UserService::class
        );
    }

    /**
     * Сервис управления пакетами
     *
     * @return PacketServiceInterface
     */
    public function packets()
    {
        return $this->resolveService(
            PacketServiceInterface::class,
            PacketService::class
        );
    }

    /**
     * Сервис управления подписками
     *
     * @return SubscriptionServiceInterface
     */
    public function subscriptions()
    {
        return $this->resolveService(
            SubscriptionServiceInterface::class,
            SubscriptionService::class
        );
    }

    /**
     * Сервис управления балансом и аккаунтами
     *
     * @return BalanceServiceInterface
     */
    public function balance()
    {
        return $this->resolveService(
            BalanceServiceInterface::class,
            BalanceService::class
        );
    }

    /**
     * Сервис управления каналами
     *
     * @return ChannelServiceInterface
     */
    public function channels()
    {
        return $this->resolveService(
            ChannelServiceInterface::class,
            ChannelService::class
        );
    }

    /**
     * Сервис управления устройствами
     *
     * @return DeviceServiceInterface
     */
    public function devices()
    {
        return $this->resolveService(
            DeviceServiceInterface::class,
            DeviceService::class
        );
    }

    /**
     * Сервис аутентификации
     *
     * @return AuthServiceInterface
     */
    public function auth()
    {
        return $this->resolveService(
            AuthServiceInterface::class,
            AuthService::class
        );
    }

    /**
     * Сервис расторжения договоров
     *
     * @return ContractServiceInterface
     */
    public function contracts()
    {
        return $this->resolveService(
            ContractServiceInterface::class,
            ContractService::class
        );
    }

    /**
     * Сервис управления тегами
     *
     * @return TagServiceInterface
     */
    public function tags()
    {
        return $this->resolveService(
            TagServiceInterface::class,
            TagService::class
        );
    }

    /**
     * Сервис промо-пакетов
     *
     * @return PromoServiceInterface
     */
    public function promo()
    {
        return $this->resolveService(
            PromoServiceInterface::class,
            PromoService::class
        );
    }

    /**
     * Сервис сообщений
     *
     * @return MessageServiceInterface
     */
    public function messages()
    {
        return $this->resolveService(
            MessageServiceInterface::class,
            MessageService::class
        );
    }

    // ==========================================
    // CALLBACK HANDLER
    // ==========================================

    /**
     * Получить обработчик callbacks
     *
     * @return CallbackHandler
     */
    public function callbacks()
    {
        if ($this->callbackHandler === null) {
            $this->callbackHandler = new CallbackHandler($this->config, $this->logger);
        }

        return $this->callbackHandler;
    }

    /**
     * Обработать входящий callback-запрос
     *
     * @param ServerRequest|null $request HTTP-запрос (если null — создаётся из суперглобалов)
     *
     * @return \TwentyFourTv\Callback\CallbackResponse
     */
    public function handleCallback(ServerRequest $request = null)
    {
        if ($request === null) {
            $request = ServerRequest::fromGlobals();
        }

        return $this->callbacks()->handle(
            $request->getUri(),
            $request->getQueryParams(),
            $request->getBody(),
            $request->getClientIp()
        );
    }

    // ==========================================
    // CONVENIENCE METHODS
    // ==========================================

    /**
     * Регистрация пользователя + подключение пакета в одной операции
     *
     * @param array $userData     Данные для регистрации
     * @param int   $packetId    ID пакета для подключения
     * @param bool  $renew       Автопродление подписки
     *
     * @throws \TwentyFourTv\Exception\TwentyFourTvException
     *
     * @return array ['user' => User, 'subscriptions' => array]
     */
    public function registerAndConnect(array $userData, $packetId, $renew = true)
    {
        $user = $this->users()->register($userData);

        $subscriptions = $this->subscriptions()->connect($user->getId(), [
            ['packet_id' => (int) $packetId, 'renew' => $renew],
        ]);

        return [
            'user'          => $user,
            'subscriptions' => $subscriptions,
        ];
    }

    // ==========================================
    // SERVICE REGISTRY
    // ==========================================

    /**
     * Зарегистрировать кастомную фабрику для сервиса
     *
     * Позволяет подменить любую реализацию сервиса без наследования Client.
     *
     * <code>
     * $client->registerService(UserServiceInterface::class, function() use ($httpClient, $logger) {
     *     return new MyCustomUserService($httpClient, $logger);
     * });
     * </code>
     *
     * @param string   $interface Полное имя интерфейса (FQCN)
     * @param callable $factory   Фабрика, возвращающая реализацию
     *
     * @return $this
     */
    public function registerService($interface, $factory)
    {
        $this->customFactories[$interface] = $factory;
        unset($this->services[$interface]);

        return $this;
    }

    // ==========================================
    // АКЦЕССОРЫ
    // ==========================================

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    // ==========================================
    // PRIVATE
    // ==========================================

    /**
     * Разрешить сервис: кастомная фабрика → инстанс по умолчанию
     *
     * @param string $interface        FQCN интерфейса
     * @param string $defaultClass     FQCN реализации по умолчанию
     *
     * @return object
     */
    private function resolveService($interface, $defaultClass)
    {
        if (!isset($this->services[$interface])) {
            if (isset($this->customFactories[$interface])) {
                $this->services[$interface] = call_user_func($this->customFactories[$interface]);
            } else {
                $this->services[$interface] = new $defaultClass($this->httpClient, $this->logger);
            }
        }

        return $this->services[$interface];
    }
}
