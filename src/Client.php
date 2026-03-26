<?php

namespace TwentyFourTv;

use TwentyFourTv\Callback\CallbackHandler;
use TwentyFourTv\Callback\CallbackResponse;
use TwentyFourTv\Contract\CallbackHandlerInterface;
use TwentyFourTv\Contract\ConfigInterface;
use TwentyFourTv\Contract\DatabaseInterface;
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
 * Главный фасад SDK 24часаТВ
 *
 * Предоставляет единую точку входа ко всем сервисам API.
 * Сервисы инициализируются лениво (lazy loading) — создаются только при первом обращении.
 *
 * <code>
 * // Создание через фабрику (рекомендуемый способ)
 * $client = ClientFactory::create('/path/to/24htv.ini', $logger);
 *
 * // Использование сервисов
 * $user = $client->users()->register(['username' => 'test', 'phone' => '+79001234567']);
 * $packets = $client->packets()->getAll();
 * $client->subscriptions()->connectSingle($user['id'], $packets[0]['id']);
 *
 * // Быстрая регистрация с подключением пакета
 * $result = $client->registerAndConnect(
 *     ['username' => 'test', 'phone' => '+79001234567'],
 *     $packetId
 * );
 * </code>
 *
 * @see ClientFactory Фабрика для создания экземпляра
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

    /** @var DatabaseInterface|null */
    private $db;

    // Ленивые сервисы
    /** @var UserServiceInterface|null */
    private $userService;

    /** @var PacketServiceInterface|null */
    private $packetService;

    /** @var SubscriptionServiceInterface|null */
    private $subscriptionService;

    /** @var BalanceServiceInterface|null */
    private $balanceService;

    /** @var ContractServiceInterface|null */
    private $contractService;

    /** @var AuthServiceInterface|null */
    private $authService;

    /** @var ChannelServiceInterface|null */
    private $channelService;

    /** @var DeviceServiceInterface|null */
    private $deviceService;

    /** @var TagServiceInterface|null */
    private $tagService;

    /** @var PromoServiceInterface|null */
    private $promoService;

    /** @var MessageServiceInterface|null */
    private $messageService;

    /** @var CallbackHandlerInterface|null */
    private $callbackHandler;

    /**
     * @param HttpClientInterface    $httpClient HTTP-клиент
     * @param ConfigInterface        $config     Конфигурация
     * @param LoggerInterface|null   $logger     Логгер
     * @param DatabaseInterface|null $db         Соединение с БД (для CallbackHandler)
     */
    public function __construct(
        HttpClientInterface $httpClient,
        ConfigInterface $config,
        LoggerInterface $logger = null,
        DatabaseInterface $db = null
    ) {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->logger = $logger;
        $this->db = $db;
    }

    // ==========================================
    // СЕРВИСЫ (Lazy Loading)
    // ==========================================

    /**
     * Управление пользователями
     *
     * @return UserServiceInterface
     */
    public function users()
    {
        if ($this->userService === null) {
            $this->userService = new UserService($this->httpClient, $this->logger);
        }

        return $this->userService;
    }

    /**
     * Управление пакетами
     *
     * @return PacketServiceInterface
     */
    public function packets()
    {
        if ($this->packetService === null) {
            $this->packetService = new PacketService($this->httpClient, $this->logger);
        }

        return $this->packetService;
    }

    /**
     * Управление подписками
     *
     * @return SubscriptionServiceInterface
     */
    public function subscriptions()
    {
        if ($this->subscriptionService === null) {
            $this->subscriptionService = new SubscriptionService($this->httpClient, $this->logger);
        }

        return $this->subscriptionService;
    }

    /**
     * Управление балансом
     *
     * @return BalanceServiceInterface
     */
    public function balance()
    {
        if ($this->balanceService === null) {
            $this->balanceService = new BalanceService($this->httpClient, $this->logger);
        }

        return $this->balanceService;
    }

    /**
     * Управление контрактами (расторжение)
     *
     * @return ContractServiceInterface
     */
    public function contracts()
    {
        if ($this->contractService === null) {
            $this->contractService = new ContractService($this->httpClient, $this->logger);
        }

        return $this->contractService;
    }

    /**
     * Аутентификация
     *
     * @return AuthServiceInterface
     */
    public function auth()
    {
        if ($this->authService === null) {
            $this->authService = new AuthService($this->httpClient, $this->logger);
        }

        return $this->authService;
    }

    /**
     * Управление каналами
     *
     * @return ChannelServiceInterface
     */
    public function channels()
    {
        if ($this->channelService === null) {
            $this->channelService = new ChannelService($this->httpClient, $this->logger);
        }

        return $this->channelService;
    }

    /**
     * Управление устройствами
     *
     * @return DeviceServiceInterface
     */
    public function devices()
    {
        if ($this->deviceService === null) {
            $this->deviceService = new DeviceService($this->httpClient, $this->logger);
        }

        return $this->deviceService;
    }

    /**
     * Управление тегами
     *
     * @return TagServiceInterface
     */
    public function tags()
    {
        if ($this->tagService === null) {
            $this->tagService = new TagService($this->httpClient, $this->logger);
        }

        return $this->tagService;
    }

    /**
     * Управление промо-пакетами
     *
     * @return PromoServiceInterface
     */
    public function promo()
    {
        if ($this->promoService === null) {
            $this->promoService = new PromoService($this->httpClient, $this->logger);
        }

        return $this->promoService;
    }

    /**
     * Управление сообщениями
     *
     * @return MessageServiceInterface
     */
    public function messages()
    {
        if ($this->messageService === null) {
            $this->messageService = new MessageService($this->httpClient, $this->logger);
        }

        return $this->messageService;
    }

    /**
     * Обработчик callback-запросов от 24ТВ
     *
     * @return CallbackHandlerInterface
     */
    public function callbacks()
    {
        if ($this->callbackHandler === null) {
            $this->callbackHandler = new CallbackHandler($this->config, $this->logger);
        }

        return $this->callbackHandler;
    }

    // ==========================================
    // SETTER INJECTION (для тестирования / переопределения)
    // ==========================================

    /**
     * @param UserServiceInterface $service
     *
     * @return $this
     */
    public function setUserService(UserServiceInterface $service)
    {
        $this->userService = $service;

        return $this;
    }

    /**
     * @param PacketServiceInterface $service
     *
     * @return $this
     */
    public function setPacketService(PacketServiceInterface $service)
    {
        $this->packetService = $service;

        return $this;
    }

    /**
     * @param SubscriptionServiceInterface $service
     *
     * @return $this
     */
    public function setSubscriptionService(SubscriptionServiceInterface $service)
    {
        $this->subscriptionService = $service;

        return $this;
    }

    /**
     * @param BalanceServiceInterface $service
     *
     * @return $this
     */
    public function setBalanceService(BalanceServiceInterface $service)
    {
        $this->balanceService = $service;

        return $this;
    }

    /**
     * @param ContractServiceInterface $service
     *
     * @return $this
     */
    public function setContractService(ContractServiceInterface $service)
    {
        $this->contractService = $service;

        return $this;
    }

    /**
     * @param AuthServiceInterface $service
     *
     * @return $this
     */
    public function setAuthService(AuthServiceInterface $service)
    {
        $this->authService = $service;

        return $this;
    }

    /**
     * @param ChannelServiceInterface $service
     *
     * @return $this
     */
    public function setChannelService(ChannelServiceInterface $service)
    {
        $this->channelService = $service;

        return $this;
    }

    /**
     * @param DeviceServiceInterface $service
     *
     * @return $this
     */
    public function setDeviceService(DeviceServiceInterface $service)
    {
        $this->deviceService = $service;

        return $this;
    }

    /**
     * @param TagServiceInterface $service
     *
     * @return $this
     */
    public function setTagService(TagServiceInterface $service)
    {
        $this->tagService = $service;

        return $this;
    }

    /**
     * @param PromoServiceInterface $service
     *
     * @return $this
     */
    public function setPromoService(PromoServiceInterface $service)
    {
        $this->promoService = $service;

        return $this;
    }

    /**
     * @param MessageServiceInterface $service
     *
     * @return $this
     */
    public function setMessageService(MessageServiceInterface $service)
    {
        $this->messageService = $service;

        return $this;
    }

    /**
     * @param CallbackHandlerInterface $handler
     *
     * @return $this
     */
    public function setCallbackHandler(CallbackHandlerInterface $handler)
    {
        $this->callbackHandler = $handler;

        return $this;
    }

    // ==========================================
    // ШОРТКАТЫ
    // ==========================================

    /**
     * Быстрая регистрация пользователя с подключением пакета
     *
     * @param array $userData Данные пользователя (username, phone, provider_uid, ...)
     * @param int   $packetId ID пакета для подключения
     * @param bool  $renew    Автопродление
     *
     * @return array ['user' => User, 'subscriptions' => array]
     */
    public function registerAndConnect(array $userData, $packetId, $renew = true)
    {
        $user = $this->users()->register($userData);
        $userId = $user->getId();

        $subscriptions = $this->subscriptions()->connectSingle($userId, $packetId, $renew);

        return [
            'user'          => $user,
            'subscriptions' => $subscriptions,
        ];
    }

    /**
     * Обработать входящий callback от 24ТВ
     *
     * @param ServerRequest|null $request Объект запроса (если null — создаётся из суперглобалов)
     *
     * @return CallbackResponse
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
    // ГЕТТЕРЫ
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
     * Получить версию SDK
     *
     * @return string
     */
    public static function getVersion()
    {
        return SdkVersion::VERSION;
    }
}
