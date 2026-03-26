// 24TV Control Panel — Application Logic
// SDK v1.0.0

const PROXY_URL = 'proxy.php';
const CHEVRON = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>';

// ═══════════════════════════════════════════════════════════
//  METHOD REGISTRY — All 11 SDK services, 100+ methods
// ═══════════════════════════════════════════════════════════

const SERVICES = {
  dashboard: {
    label: 'Дашборд', icon: '📊', isDashboard: true, methods: []
  },
  users: {
    label: 'Пользователи', icon: '👤',
    methods: [
      { name:'getById', label:'Получить по ID', http:'GET', endpoint:'/users/{id}',
        desc:'Возвращает полную информацию о пользователе по его внутреннему ID в системе 24ТВ. В ответе содержатся: логин, телефон, email, ФИО, provider_uid (лицевой счёт), статус блокировки (is_active), дата регистрации, привязанные теги и другие атрибуты абонента.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'getAll', label:'Список пользователей', http:'GET', endpoint:'/users',
        desc:'Возвращает постраничный список всех пользователей, зарегистрированных у данного провайдера. Поддерживает пагинацию через параметры limit и offset. По умолчанию limit=20. Полезно для экспорта базы абонентов и построения отчётов.',
        fields:[{n:'limit',l:'Limit',ph:'20'},{n:'offset',l:'Offset',ph:'0'}] },
      { name:'findByPhone', label:'Поиск по телефону', http:'GET', endpoint:'/users?phone=',
        desc:'Ищет пользователя по точному совпадению номера телефона. Формат номера: 7XXXXXXXXXX (без «+», без пробелов, 11 цифр). Если найдено несколько совпадений — возвращается массив. Используется при идентификации абонента по звонку в техподдержку.',
        fields:[{n:'phone',l:'Телефон',req:true,ph:'79001234567'}] },
      { name:'findByProviderUid', label:'Поиск по ЛС', http:'GET', endpoint:'/users?provider_uid=',
        desc:'Ищет пользователя по номеру лицевого счёта оператора (provider_uid). Это основной идентификатор абонента в биллинге провайдера. Возвращает объект пользователя или массив, если ЛС привязан к нескольким аккаунтам.',
        fields:[{n:'provider_uid',l:'Provider UID',req:true,ph:'51216'}] },
      { name:'findByEmail', label:'Поиск по email', http:'GET', endpoint:'/users?email=',
        desc:'Ищет пользователя по точному совпадению адреса электронной почты. Регистр не учитывается. Полезно для восстановления доступа и верификации абонента.',
        fields:[{n:'email',l:'Email',req:true,ph:'user@example.com'}] },
      { name:'findByUsername', label:'Поиск по username', http:'GET', endpoint:'/users?username=',
        desc:'Ищет пользователя по логину (username). Логин уникален в системе и обычно совпадает с номером телефона. Используется для проверки существования аккаунта перед регистрацией.',
        fields:[{n:'username',l:'Username',req:true,ph:'79035411296'}] },
      { name:'search', label:'Полнотекстовый поиск', http:'GET', endpoint:'/users?search=',
        desc:'Глобальный поиск по всем текстовым полям пользователя: логин, телефон, email, ФИО, provider_uid. Поддерживает частичное совпадение. Идеально для быстрого поиска абонента в техподдержке когда точный идентификатор неизвестен.',
        fields:[{n:'search',l:'Строка поиска',req:true,ph:'Иванов'},{n:'limit',l:'Limit',ph:'20'},{n:'offset',l:'Offset',ph:'0'}] },
      { name:'register', label:'Регистрация', http:'POST', endpoint:'/users', danger:true,
        desc:'⚠️ Создаёт нового абонента в системе 24ТВ. Обязательные поля: username (уникальный логин) и phone (номер телефона). Дополнительно можно указать ФИО, email и provider_uid для привязки к лицевому счёту биллинга. После регистрации абонент получает ID и может подключать пакеты.',
        fields:[{n:'username',l:'Username',req:true,ph:'user_12345'},{n:'phone',l:'Телефон',req:true,ph:'+79001234567'},{n:'first_name',l:'Имя'},{n:'last_name',l:'Фамилия'},{n:'email',l:'Email'},{n:'provider_uid',l:'Provider UID'}] },
      { name:'update', label:'Обновить данные', http:'PATCH', endpoint:'/users/{id}', danger:true,
        desc:'⚠️ Изменяет персональные данные пользователя. Можно обновить любое из полей: имя, фамилию, email, телефон, provider_uid, пароль. Передавайте только те поля, которые нужно изменить — остальные останутся без изменений (PATCH-семантика).',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'first_name',l:'Имя'},{n:'last_name',l:'Фамилия'},{n:'provider_uid',l:'Provider UID'},{n:'password',l:'Пароль'}] },
      { name:'block', label:'Блокировка', http:'PATCH', endpoint:'/users/{id}', danger:true,
        desc:'⚠️ Блокирует пользователя — устанавливает is_active=false. Заблокированный абонент теряет доступ к просмотру каналов и личному кабинету. Используется при неоплате, нарушении условий договора или по запросу абонента. Подписки при этом сохраняются.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'unblock', label:'Разблокировка', http:'PATCH', endpoint:'/users/{id}',
        desc:'Снимает блокировку пользователя — устанавливает is_active=true. Абонент восстанавливает доступ к просмотру каналов и личному кабинету. Все ранее оформленные подписки продолжают действовать.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'delete', label:'Деактивация (DELETE)', http:'DELETE', endpoint:'/users/{id}', danger:true,
        desc:'⚠️ Деактивирует аккаунт пользователя. Аккаунт помечается как удалённый, но физически данные сохраняются в системе. Все активные подписки будут отключены. Операция может быть обратима через обращение к API 24ТВ.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'archive', label:'Архивирование', http:'DELETE', endpoint:'/users/{id}/archive', danger:true,
        desc:'⚠️ Полное архивирование аккаунта пользователя. В отличие от деактивации, архивирование предполагает длительное хранение данных без возможности быстрого восстановления. Используется при расторжении договора или миграции абонента к другому провайдеру.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
    ]
  },
  packets: {
    label: 'Пакеты', icon: '📦',
    methods: [
      { name:'getHierarchical', label:'Иерархичный список', http:'GET', endpoint:'/packets',
        desc:'Возвращает дерево пакетов провайдера в иерархическом виде: базовые пакеты содержат вложенные дополнительные. Параметр includes позволяет подгрузить связанные данные (availables — доступные допы, channels — список каналов в пакете).',
        fields:[{n:'includes',l:'Includes',ph:'availables,channels'}] },
      { name:'getById', label:'Пакет по ID', http:'GET', endpoint:'/packets/{id}',
        desc:'Возвращает детальную информацию о конкретном пакете: название, описание, цена, тип (базовый/дополнительный), список каналов (при includes=channels). Используйте для отображения карточки пакета абоненту.',
        fields:[{n:'packetId',l:'Packet ID',req:true,ph:'498'},{n:'includes',l:'Includes',ph:'channels'}] },
      { name:'getFlat', label:'Плоский список', http:'GET', endpoint:'/packets/flat',
        desc:'Возвращает плоский (не иерархический) список пакетов. Фильтр is_base: true — только базовые, false — только дополнительные, не указан — все. Удобно для построения выпадающих списков в интерфейсе.',
        fields:[{n:'is_base',l:'is_base',ph:'true или false'}] },
      { name:'getBase', label:'Только базовые', http:'GET', endpoint:'/packets?is_base=true',
        desc:'Возвращает только базовые пакеты провайдера. Базовый пакет — основной тарифный план, к которому подключаются дополнительные пакеты каналов. У абонента должен быть хотя бы один активный базовый пакет.',
        fields:[] },
      { name:'getAdditional', label:'Только дополнительные', http:'GET', endpoint:'/packets?is_base=false',
        desc:'Возвращает только дополнительные пакеты каналов. Дополнительные пакеты подключаются поверх базового и расширяют набор доступных каналов (спорт, кино, детские и т.д.).',
        fields:[] },
      { name:'getAllWithAvailables', label:'С доп. пакетами', http:'GET', endpoint:'/packets?includes=availables',
        desc:'Возвращает список пакетов, в котором для каждого базового пакета подгружены доступные дополнительные пакеты (поле availables). Полезно для отображения полной связки: базовый → какие допы к нему можно подключить.',
        fields:[] },
      { name:'getAllWithChannels', label:'С каналами', http:'GET', endpoint:'/packets?includes=availables,channels',
        desc:'Расширенный вариант: пакеты + доступные допы + список каналов в каждом пакете. Самый полный запрос пакетной сетки. Используется для построения витрины пакетов с детализацией до каналов.',
        fields:[] },
      { name:'getPurchases', label:'Покупки пакета', http:'GET', endpoint:'/packets/{id}/purchases',
        desc:'Возвращает историю покупок (подключений) указанного пакета. Содержит информацию о пользователях, которые приобрели данный пакет, даты начала и окончания подписок.',
        fields:[{n:'packetId',l:'Packet ID',req:true,ph:'80'}] },
      { name:'getPurchasePeriods', label:'Периоды покупок', http:'GET', endpoint:'/packets/{id}/purchaseperiods',
        desc:'Возвращает доступные периоды подключения пакета (1 месяц, 3 месяца, 6 месяцев, год и т.д.) с ценами за каждый период. Используется для отображения тарифных опций при подключении пакета.',
        fields:[{n:'packetId',l:'Packet ID',req:true,ph:'499'}] },
      { name:'getUserPackets', label:'Пакеты пользователя', http:'GET', endpoint:'/users/{id}/packets',
        desc:'Возвращает список персональных (индивидуальных) пакетов, созданных для конкретного пользователя. Персональные пакеты — это модифицированные копии стандартных пакетов с индивидуальной ценой или составом каналов.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'getUserPacketById', label:'Пакет пользователя по ID', http:'GET', endpoint:'/users/{id}/packets/{packetId}',
        desc:'Возвращает детальную информацию о конкретном персональном пакете пользователя. Содержит: индивидуальное название, цену, описание и привязку к исходному пакету.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'packetId',l:'Packet ID',req:true,ph:'498'}] },
      { name:'createUserPacket', label:'Создать персональный', http:'POST', endpoint:'/users/{id}/packets', danger:true,
        desc:'⚠️ Создаёт персональный пакет для пользователя на основе существующего пакета. Позволяет задать индивидуальное название, цену и описание. Используется для VIP-абонентов или специальных условий подключения.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'packet_id',l:'Packet ID',req:true,ph:'498'},{n:'name',l:'Название',ph:'VIP пакет'},{n:'price',l:'Цена',ph:'299.00'},{n:'description',l:'Описание'}] },
      { name:'updateUserPacket', label:'Изменить персональный', http:'PATCH', endpoint:'/users/{id}/packets/{packetId}', danger:true,
        desc:'⚠️ Изменяет параметры персонального пакета пользователя. Можно обновить название, цену или описание. Изменения вступают в силу немедленно.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'packetId',l:'Packet ID',req:true,ph:'498'},{n:'name',l:'Название'},{n:'price',l:'Цена'}] },
      { name:'deleteUserPacket', label:'Удалить персональный', http:'DELETE', endpoint:'/users/{id}/packets/{packetId}', danger:true,
        desc:'⚠️ Удаляет персональный пакет пользователя. Если у абонента есть активная подписка на этот пакет, она будет отключена. Стандартные пакеты провайдера не затрагиваются.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'packetId',l:'Packet ID',req:true,ph:'498'}] },
    ]
  },
  subscriptions: {
    label: 'Подписки', icon: '🔗',
    methods: [
      { name:'getCurrent', label:'Текущие', http:'GET', endpoint:'/users/{id}/subscriptions/current',
        desc:'Возвращает только текущие активные подписки пользователя на данный момент. Включает информацию о пакете, дате начала и окончания, статусе автопродления. Самый частый запрос при обращении абонента в техподдержку.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'getAll', label:'Все подписки', http:'GET', endpoint:'/users/{id}/subscriptions',
        desc:'Возвращает все подписки пользователя с возможностью фильтрации. Параметр types: all (все), current (текущие), active (активные), paused (на паузе), planned (запланированные). Параметр includes=packet.channels подгружает каналы из пакета подписки.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'types',l:'Тип фильтра',ph:'all, current, active, paused, planned'},{n:'includes',l:'Includes',ph:'packet.channels'}] },
      { name:'getById', label:'По ID', http:'GET', endpoint:'/users/{id}/subscriptions/{subId}',
        desc:'Возвращает детальную информацию о конкретной подписке по её ID. Содержит: packet_id, даты начала/окончания, статус автопродления (renew), статус паузы. Параметр includes позволяет подгрузить данные пакета.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'subscriptionId',l:'Subscription ID',req:true,ph:'45230'},{n:'includes',l:'Includes'}] },
      { name:'getActive', label:'Активные', http:'GET', endpoint:'/users/{id}/subscriptions?types=active',
        desc:'Возвращает только активные подписки — те, что сейчас действуют и не приостановлены. Активная подписка означает, что абонент имеет доступ к каналам этого пакета.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'getPaused', label:'На паузе', http:'GET', endpoint:'/users/{id}/subscriptions?types=paused',
        desc:'Возвращает подписки, которые временно приостановлены (на паузе). Во время паузы абонент не имеет доступа к каналам пакета, но подписка не отключается. Пауза может иметь дату окончания.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'getPlanned', label:'Запланированные', http:'GET', endpoint:'/users/{id}/subscriptions?types=planned',
        desc:'Возвращает запланированные подписки — те, что уже оформлены, но ещё не начали действовать (дата начала в будущем). Используется при предварительном подключении пакетов.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'getFuture', label:'Будущие', http:'GET', endpoint:'/users/{id}/futures',
        desc:'Возвращает подписки, запланированные на будущее. Отличается от «запланированных» тем, что включает подписки, которые автоматически активируются после окончания текущего периода.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'connect', label:'Подключить (массив)', http:'POST', endpoint:'/users/{id}/subscriptions', danger:true,
        desc:'⚠️ Подключает одну или несколько подписок пользователю. Принимает JSON-массив объектов, каждый из которых содержит packet_id и опционально renew (автопродление), start_at (дата начала), end_at (дата окончания). Пример: [{"packet_id":80,"renew":true}].',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'subscriptions',l:'Подписки (JSON)',type:'json',req:true,ph:'[{"packet_id":80,"renew":true}]'}] },
      { name:'connectSingle', label:'Подключить пакет', http:'POST', endpoint:'/users/{id}/subscriptions', danger:true,
        desc:'⚠️ Упрощённое подключение одного пакета. Укажите packet_id и настройки: renew (true/false для автопродления), start_at и end_at (ISO-формат дат) для задания периода действия. Если даты не указаны — подписка начнётся немедленно.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'packetId',l:'Packet ID',req:true,ph:'498'},{n:'renew',l:'Автопродление',ph:'true или false'},{n:'start_at',l:'Начало (ISO)',ph:'2025-01-01T00:00:00'},{n:'end_at',l:'Окончание (ISO)',ph:'2025-02-01T00:00:00'}] },
      { name:'disconnect', label:'Отключить', http:'DELETE', endpoint:'/users/{id}/subscriptions/{subId}', danger:true,
        desc:'⚠️ Отключает конкретную подписку пользователя по ID подписки. Подписка немедленно деактивируется, абонент теряет доступ к каналам этого пакета. Для отключения всех подписок используйте массовые операции.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'subscriptionId',l:'Subscription ID',req:true,ph:'45230'}] },
      { name:'update', label:'Обновить', http:'PATCH', endpoint:'/users/{id}/subscriptions/{subId}', danger:true,
        desc:'⚠️ Обновляет параметры существующей подписки. Можно изменить: packet_id (перевести на другой пакет), renew (вкл/выкл автопродление), даты действия. Используется для смены тарифного плана без отключения/подключения.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'subscriptionId',l:'Subscription ID',req:true,ph:'45230'},{n:'packet_id',l:'Packet ID'},{n:'renew',l:'Renew',ph:'true или false'}] },
      { name:'disableRenew', label:'Откл. автопродление', http:'PATCH', endpoint:'/users/{id}/subscriptions/{subId}', danger:true,
        desc:'⚠️ Отключает автоматическое продление подписки. После окончания текущего оплаченного периода подписка не будет продлена и доступ к каналам прекратится. Абонент может повторно подключить пакет вручную.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'subscriptionId',l:'Subscription ID',req:true,ph:'45230'},{n:'packetId',l:'Packet ID',req:true,ph:'498'}] },

      { name:'pause', label:'Пауза', http:'POST', endpoint:'/users/{id}/subscriptions/{subId}/pauses', danger:true,
        desc:'⚠️ Ставит конкретную подписку на паузу. Во время паузы доступ к каналам пакета приостанавливается, но подписка сохраняется. Пауза может быть снята вручную или автоматически по истечении указанного срока.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'subscriptionId',l:'Subscription ID',req:true,ph:'45230'}] },
      { name:'pauseUser', label:'Пауза пользователя', http:'POST', endpoint:'/users/{id}/pauses', danger:true,
        desc:'⚠️ Ставит пользователя на паузу (замораживает все его активные подписки). Используется при временной приостановке услуги (отпуск абонента, технические работы). Все каналы становятся недоступны.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'getPauses', label:'Список пауз', http:'GET', endpoint:'/users/{id}/subscriptions/{subId}/pauses',
        desc:'Возвращает список всех пауз для конкретной подписки. Каждая пауза содержит: ID, дату начала, дату окончания (если установлена), и текущий статус. Полезно для анализа истории приостановок.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'subscriptionId',l:'Subscription ID',req:true,ph:'45230'}] },
      { name:'unpause', label:'Снять паузу', http:'DELETE', endpoint:'/users/{id}/.../pauses/{pauseId}', danger:true,
        desc:'⚠️ Снимает конкретную паузу с подписки по ID паузы. Подписка возобновляется, абонент восстанавливает доступ к каналам. Для снятия всех пауз используйте метод «Снять все паузы».',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'subscriptionId',l:'Subscription ID',req:true,ph:'45230'},{n:'pauseId',l:'Pause ID',req:true,ph:'1234'}] },
      { name:'unpauseAll', label:'Снять все паузы', http:'DELETE', endpoint:'/users/{id}/pauses/delete', danger:true,
        desc:'⚠️ Снимает ВСЕ паузы со всех подписок пользователя одновременно. Все приостановленные подписки возобновляются, доступ к каналам восстанавливается в полном объёме.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'updatePauseDate', label:'Изменить дату паузы', http:'PATCH', endpoint:'/users/{id}/.../pauses/{pauseId}', danger:true,
        desc:'⚠️ Изменяет дату окончания паузы. Используется для продления или сокращения периода приостановки. Дата указывается в ISO-формате (YYYY-MM-DDTHH:MM:SS). По достижении этой даты пауза автоматически снимается.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'subscriptionId',l:'Subscription ID',req:true,ph:'45230'},{n:'pauseId',l:'Pause ID',req:true,ph:'1234'},{n:'end_at',l:'Новая дата (ISO)',req:true,ph:'2025-02-01T00:00:00'}] },
      { name:'personalize', label:'Персонализация', http:'POST', endpoint:'/users/{id}/packets', danger:true,
        desc:'⚠️ Создаёт персональный пакет для пользователя. Аналог метода из раздела «Пакеты», но вызывается в контексте управления подписками. Позволяет настроить индивидуальную цену, название и описание пакета для конкретного абонента.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'packet_id',l:'Packet ID',req:true,ph:'498'},{n:'name',l:'Название',ph:'Индивидуальный'},{n:'price',l:'Цена',ph:'199.00'},{n:'description',l:'Описание'}] },
    ]
  },
  balance: {
    label: 'Баланс', icon: '💰',
    methods: [
      { name:'get', label:'Получить баланс', http:'GET', endpoint:'/users/{id}/provider/account',
        desc:'Возвращает текущий баланс лицевого счёта пользователя у провайдера. Внимание: User ID — это внутренний ID пользователя в системе 24ТВ, а НЕ номер лицевого счёта (provider_uid). Для поиска по ЛС используйте метод «Баланс по ЛС».',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'}] },
      { name:'getByProviderUid', label:'Баланс по ЛС', http:'GET', endpoint:'/users → balance',
        desc:'Комбинированный запрос: сначала находит пользователя по номеру лицевого счёта (provider_uid), затем возвращает его баланс. Удобно для техподдержки — не нужно знать внутренний ID пользователя, достаточно номера ЛС из биллинга.',
        fields:[{n:'provider_uid',l:'Provider UID (ЛС)',req:true,ph:'32240'}] },
      { name:'set', label:'Установить баланс', http:'POST', endpoint:'/users/{id}/provider/account', danger:true,
        desc:'⚠️ Устанавливает баланс лицевого счёта пользователя. Требует billing_id (идентификатор биллинговой системы) и amount (сумма). Используется для корректировки баланса, зачисления оплаты или возврата средств.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'},{n:'billingId',l:'Billing ID',req:true,ph:'1'},{n:'amount',l:'Сумма',req:true,ph:'500.00'}] },
      { name:'getProviderAccounts', label:'Аккаунты провайдера', http:'GET', endpoint:'/users/{id}/provider/accounts',
        desc:'Возвращает список всех биллинговых аккаунтов пользователя у провайдера. Каждый аккаунт содержит ID, баланс и тип. У пользователя может быть несколько аккаунтов (основной, бонусный и т.д.).',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'}] },
      { name:'setProviderAccounts', label:'Установить аккаунты', http:'POST', endpoint:'/users/{id}/provider/accounts', danger:true,
        desc:'⚠️ Устанавливает данные биллинговых аккаунтов пользователя. Принимает JSON-массив объектов с полями id и amount. Позволяет массово обновить балансы нескольких аккаунтов за один запрос.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'},{n:'accounts',l:'Аккаунты (JSON)',type:'json',ph:'[{"id":"12345","amount":"500.00"}]'}] },
      { name:'getAccounts', label:'Платёжные аккаунты', http:'GET', endpoint:'/users/{id}/accounts',
        desc:'Возвращает список внутренних платёжных аккаунтов пользователя в системе 24ТВ. Отличается от аккаунтов провайдера — это аккаунты внутренней платёжной системы. Содержит ID аккаунта, баланс и историю транзакций.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'}] },
      { name:'createAccount', label:'Создать аккаунт', http:'POST', endpoint:'/users/{id}/accounts', danger:true,
        desc:'⚠️ Создаёт новый внутренний платёжный аккаунт для пользователя. Можно указать начальную сумму. Используется для организации раздельного учёта средств (например, основной счёт и бонусный).',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'},{n:'id',l:'ID аккаунта',ph:'bonus-1'},{n:'amount',l:'Сумма',ph:'0'}] },
      { name:'getTransactions', label:'Транзакции', http:'GET', endpoint:'/users/{id}/accounts/{accountId}/transactions',
        desc:'Возвращает историю всех транзакций по указанному платёжному аккаунту. Каждая транзакция содержит: сумму, тип (credit/debit), дату, описание. Используется для разбора спорных ситуаций с оплатой.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'},{n:'accountId',l:'Account ID',req:true,ph:'1'}] },
      { name:'createTransaction', label:'Создать транзакцию', http:'POST', endpoint:'/users/{id}/accounts/{accountId}/transactions', danger:true,
        desc:'⚠️ Создаёт новую транзакцию на платёжном аккаунте. Тип credit — зачисление (пополнение), debit — списание. Сумма указывается числом. Все транзакции фиксируются в истории и не могут быть удалены.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'},{n:'accountId',l:'Account ID',req:true,ph:'1'},{n:'amount',l:'Сумма',ph:'100.00'},{n:'type',l:'Тип (credit/debit)',ph:'credit'}] },
      { name:'getPaymentSources', label:'Платёжные источники', http:'GET', endpoint:'/paymentsources',
        desc:'Возвращает список доступных источников оплаты (платёжных систем), настроенных для провайдера. Содержит ID и название каждого источника. Не требует параметров.',
        fields:[] },
      { name:'getEntityLicenses', label:'Лицензии', http:'GET', endpoint:'/users/{id}/entity_licenses',
        desc:'Возвращает список лицензий (дополнительных прав доступа), привязанных к пользователю. Лицензии могут давать доступ к дополнительному контенту, функциям или сервисам помимо стандартных пакетов.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'}] },
      { name:'addEntityLicense', label:'Добавить лицензию', http:'POST', endpoint:'/users/{id}/entity_licenses', danger:true,
        desc:'⚠️ Привязывает лицензию к пользователю. Требуется ID лицензии из каталога доступных лицензий. После привязки пользователь получает дополнительные права, определённые этой лицензией.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'},{n:'entity_license_id',l:'License ID',req:true,ph:'5'}] },
      { name:'removeEntityLicense', label:'Удалить лицензию', http:'DELETE', endpoint:'/users/{id}/entity_licenses/{licenseId}', danger:true,
        desc:'⚠️ Отвязывает лицензию от пользователя. После удаления пользователь теряет дополнительные права, предоставляемые этой лицензией. Основные подписки не затрагиваются.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'350666'},{n:'licenseId',l:'License ID',req:true,ph:'5'}] },
    ]
  },
  contracts: {
    label: 'Контракт', icon: '📄',
    methods: [
      { name:'terminate', label:'Расторжение договора', http:'PUT', endpoint:'/users/{id}/change_provider/1', danger:true,
        desc:'⚠️ НЕОБРАТИМАЯ ОПЕРАЦИЯ! Расторгает договор абонента с провайдером и переводит его на прямое обслуживание 24часаТВ. После выполнения провайдер полностью теряет управление этим абонентом. Используйте только по прямому указанию руководства.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
    ]
  },
  auth: {
    label: 'Авторизация', icon: '🔑',
    methods: [
      { name:'getProviderToken', label:'Получить access_token', http:'POST', endpoint:'/auth/provider',
        desc:'Генерирует access_token для авторизации конкретного пользователя. Токен используется для воспроизведения стримов, доступа к личному кабинету и API от имени абонента. Укажите один из идентификаторов: user_id, username или phone.',
        fields:[{n:'user_id',l:'User ID',ph:'350666'},{n:'username',l:'Username',ph:'user_12345'},{n:'phone',l:'Телефон',ph:'79001234567'}] },
    ]
  },
  channels: {
    label: 'Каналы', icon: '📺',
    methods: [
      { name:'getAll', label:'Список каналов', http:'GET', endpoint:'/channels',
        desc:'Возвращает постраничный список всех ТВ-каналов, доступных у провайдера. Поддерживает поиск по названию и пагинацию. Параметр includes=images.blackback подгружает изображения каналов (логотипы на чёрном фоне).',
        fields:[{n:'search',l:'Поиск',ph:'Первый канал'},{n:'limit',l:'Limit',ph:'20'},{n:'offset',l:'Offset',ph:'0'},{n:'includes',l:'Includes',ph:'images.blackback'}] },
      { name:'getById', label:'Канал по ID', http:'GET', endpoint:'/channels/{id}',
        desc:'Возвращает детальную информацию о конкретном ТВ-канале: название, описание, номер, логотип, жанр, доступность и текущий эфир. Используется для отображения карточки канала.',
        fields:[{n:'channelId',l:'Channel ID',req:true,ph:'36'}] },
      { name:'getSchedule', label:'Расписание', http:'GET', endpoint:'/channels/{id}/schedule',
        desc:'Возвращает расписание (телепрограмму) канала на указанную дату. Каждый элемент содержит: время начала/окончания, название передачи, описание. Если дата не указана — возвращается программа на сегодня.',
        fields:[{n:'channelId',l:'Channel ID',req:true,ph:'36'},{n:'date',l:'Дата (YYYY-MM-DD)',ph:'2025-01-15'},{n:'limit',l:'Limit',ph:'20'}] },
      { name:'getContentSchedule', label:'Контентное расписание', http:'GET', endpoint:'/channels/{id}/content_schedule',
        desc:'Возвращает контентное расписание канала — расширенная версия обычного расписания с подробными метаданными контента: жанр, рейтинг, продолжительность, постер.',
        fields:[{n:'channelId',l:'Channel ID',req:true,ph:'36'},{n:'date',l:'Дата',ph:'2025-01-15'}] },
      { name:'getStream', label:'Стрим', http:'GET', endpoint:'/channels/{id}/stream',
        desc:'Возвращает URL потока вещания канала. Требует access_token пользователя (полученный через auth/provider). Тип потока: hls (адаптивный), local, http. Поддерживает параметры: ts (timestamp для таймшифта), history, preview, force_https.',
        fields:[{n:'channelId',l:'Channel ID',req:true,ph:'36'},{n:'type',l:'Тип',ph:'hls'},{n:'ts',l:'Timestamp',ph:'0'},{n:'access_token',l:'Access Token',ph:'eyJ...'},{n:'history',l:'История'},{n:'preview',l:'Превью'},{n:'force_https',l:'HTTPS'}] },
      { name:'getCategories', label:'Категории', http:'GET', endpoint:'/channels/categories',
        desc:'Возвращает список категорий (жанров) каналов: спорт, кино, новости, детские и т.д. Параметр includes подгружает связанные данные. Параметр search фильтрует по названию категории.',
        fields:[{n:'includes',l:'Includes'},{n:'search',l:'Поиск',ph:'спорт'}] },
      { name:'getCategoryList', label:'Категории (v3)', http:'GET', endpoint:'/channels/category_list',
        desc:'Расширенная версия списка категорий (API v3). Возвращает категории с пагинацией и возможностью подгрузки дополнительных данных через includes.',
        fields:[{n:'includes',l:'Includes'},{n:'limit',l:'Limit',ph:'20'},{n:'offset',l:'Offset',ph:'0'}] },
      { name:'getChannelList', label:'Список (v3)', http:'GET', endpoint:'/channels/channel_list',
        desc:'Расширенный список каналов (API v3). Аналог getAll, но с дополнительными полями и улучшенной фильтрацией. Поддерживает параметр includes для подгрузки связанных данных.',
        fields:[{n:'includes',l:'Includes'},{n:'limit',l:'Limit',ph:'20'},{n:'offset',l:'Offset',ph:'0'}] },
      { name:'getFreeList', label:'Бесплатные', http:'GET', endpoint:'/channels/free_list',
        desc:'Возвращает список бесплатных ТВ-каналов, доступных без подписки. Эти каналы доступны всем зарегистрированным пользователям вне зависимости от подключённых пакетов.',
        fields:[] },
      { name:'getPackets', label:'Пакеты канала', http:'GET', endpoint:'/channels/{id}/packets',
        desc:'Возвращает список пакетов, в которые входит указанный канал. Полезно для ответа на вопрос абонента: «Какой пакет подключить, чтобы смотреть этот канал?».',
        fields:[{n:'channelId',l:'Channel ID',req:true,ph:'36'}] },
      { name:'getQuickSalesPackets', label:'Быстрая продажа', http:'GET', endpoint:'/channels/{id}/quick_sales_packets',
        desc:'Возвращает пакеты для быстрой продажи, содержащие указанный канал. Пакеты быстрой продажи — это упрощённые тарифы для оперативного подключения через техподдержку или самообслуживание.',
        fields:[{n:'channelId',l:'Channel ID',req:true,ph:'36'}] },
      { name:'getPurchasePacketShort', label:'Покупки (кратко)', http:'GET', endpoint:'/channels/{id}/purchasepacket_short',
        desc:'Возвращает краткую информацию о покупках пакетов, связанных с каналом. Сокращённая версия для отображения в интерфейсе без избыточных данных.',
        fields:[{n:'channelId',l:'Channel ID',req:true,ph:'36'}] },
      { name:'getUserChannelList', label:'Каналы юзера (v3)', http:'GET', endpoint:'/users/self/channel_list',
        desc:'Возвращает список каналов, доступных текущему авторизованному пользователю (API v3). Включает каналы из всех активных подписок. Требует access_token. Поддерживает поиск и пагинацию.',
        fields:[{n:'includes',l:'Includes'},{n:'search',l:'Поиск',ph:'Первый канал'},{n:'limit',l:'Limit',ph:'20'}] },
      { name:'getUserChannels', label:'Доступные каналы', http:'GET', endpoint:'/users/self/channels',
        desc:'Возвращает все каналы, доступные текущему пользователю. Объединяет каналы из всех активных подписок и бесплатные каналы. Требует access_token. Поддерживает поиск и includes.',
        fields:[{n:'includes',l:'Includes'},{n:'search',l:'Поиск',ph:'НТВ'},{n:'limit',l:'Limit',ph:'20'}] },
    ]
  },
  devices: {
    label: 'Устройства', icon: '📱',
    methods: [
      { name:'getAll', label:'Все устройства', http:'GET', endpoint:'/devices',
        desc:'Возвращает список всех устройств, зарегистрированных в системе провайдера. Поддерживает фильтрацию по: provider_uid (ЛС абонента), serial (серийный номер), device_type (тип: stb, tv, mobile). Пагинация через limit/offset.',
        fields:[{n:'provider_uid',l:'Provider UID'},{n:'serial',l:'Серийный №',ph:'SN123456'},{n:'device_type',l:'Тип',ph:'stb, tv, mobile'},{n:'limit',l:'Limit',ph:'20'},{n:'offset',l:'Offset',ph:'0'}] },
      { name:'create', label:'Создать устройство', http:'POST', endpoint:'/devices', danger:true,
        desc:'⚠️ Регистрирует новое устройство в системе. Укажите серийный номер, тип устройства (stb — приставка, tv — телевизор, mobile — мобильное) и MAC-адрес сетевого интерфейса. Устройство может быть затем привязано к пользователю.',
        fields:[{n:'serial',l:'Серийный №',ph:'SN123456'},{n:'device_type',l:'Тип',ph:'stb'},{n:'interface_mac',l:'MAC',ph:'AA:BB:CC:DD:EE:FF'}] },
      { name:'getUserDevices', label:'Устройства юзера', http:'GET', endpoint:'/users/{id}/devices',
        desc:'Возвращает список всех устройств, привязанных к конкретному пользователю. Каждое устройство содержит: ID, серийный номер, тип, MAC-адрес, дату привязки. Полезно для диагностики проблем с воспроизведением.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'getUserDevice', label:'Устройство по ID', http:'GET', endpoint:'/users/{id}/devices/{deviceId}',
        desc:'Возвращает детальную информацию о конкретном устройстве пользователя по ID устройства. Содержит все атрибуты: серийный номер, тип, MAC, статус, дату регистрации.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'deviceId',l:'Device ID',req:true,ph:'1'}] },
      { name:'getUserDeviceByToken', label:'По access_token', http:'GET', endpoint:'/users/{id}/devices/device',
        desc:'Возвращает устройство пользователя, с которого был получен указанный access_token. Используется для определения, с какого устройства абонент в данный момент смотрит ТВ.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'access_token',l:'Access Token',req:true,ph:'eyJ...'}] },
      { name:'deleteUserDevice', label:'Удалить устройство', http:'DELETE', endpoint:'/users/{id}/devices/{deviceId}', danger:true,
        desc:'⚠️ Отвязывает устройство от пользователя и удаляет его. Абонент не сможет продолжать просмотр на этом устройстве до повторной привязки. Используется при смене оборудования или обнаружении несанкционированного доступа.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'deviceId',l:'Device ID',req:true,ph:'1'}] },
    ]
  },
  tags: {
    label: 'Теги', icon: '🏷️',
    methods: [
      { name:'getAll', label:'Список тегов', http:'GET', endpoint:'/tags',
        desc:'Возвращает список всех тегов провайдера. Теги используются для группировки пользователей по категориям: тариф, район, тип подключения и т.д. Поддерживает фильтрацию по имени (name), поиск (search) и короткому имени (shortname).',
        fields:[{n:'name',l:'Имя',ph:'vip'},{n:'search',l:'Поиск',ph:'тариф'},{n:'shortname',l:'Короткое имя',ph:'vip'}] },
      { name:'getById', label:'Тег по ID', http:'GET', endpoint:'/tags/{id}',
        desc:'Возвращает детальную информацию о теге по его ID: название, короткое имя, описание и количество привязанных пользователей.',
        fields:[{n:'tagId',l:'Tag ID',req:true,ph:'1'}] },
      { name:'create', label:'Создать тег', http:'POST', endpoint:'/tags', danger:true,
        desc:'⚠️ Создаёт новый тег для группировки пользователей. Укажите название (обязательно) и опционально короткое имя. Теги позволяют сегментировать абонентскую базу для маркетинговых акций или технического управления.',
        fields:[{n:'name',l:'Название',req:true,ph:'VIP-клиенты'},{n:'shortname',l:'Короткое имя',ph:'vip'}] },
      { name:'update', label:'Изменить тег', http:'PATCH', endpoint:'/tags/{id}', danger:true,
        desc:'⚠️ Изменяет параметры существующего тега. Можно обновить название и/или короткое имя. Изменения автоматически отражаются у всех пользователей, которым присвоен этот тег.',
        fields:[{n:'tagId',l:'Tag ID',req:true,ph:'1'},{n:'name',l:'Название',ph:'Новое название'},{n:'shortname',l:'Короткое имя'}] },
      { name:'delete', label:'Удалить тег', http:'DELETE', endpoint:'/tags/{id}', danger:true,
        desc:'⚠️ Удаляет тег. Тег автоматически снимается со всех пользователей, которым он был присвоен. Удаление необратимо — при необходимости придётся создавать тег заново и переназначать пользователям.',
        fields:[{n:'tagId',l:'Tag ID',req:true,ph:'1'}] },
      { name:'addToUser', label:'Добавить юзеру', http:'POST', endpoint:'/users/{id}/tags', danger:true,
        desc:'⚠️ Присваивает тег пользователю. Один пользователь может иметь неограниченное количество тегов. Теги используются для фильтрации, отчётности и автоматизации бизнес-процессов.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'tag_id',l:'Tag ID',req:true,ph:'1'}] },
      { name:'removeFromUser', label:'Удалить у юзера', http:'DELETE', endpoint:'/users/{id}/tags/{tagId}', danger:true,
        desc:'⚠️ Снимает тег с пользователя. Тег остаётся в системе и может быть повторно назначен. Другие пользователи с этим тегом не затрагиваются.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'tagId',l:'Tag ID',req:true,ph:'1'}] },
    ]
  },
  promo: {
    label: 'Промо', icon: '🎁',
    methods: [
      { name:'getPackets', label:'Промо-пакеты', http:'GET', endpoint:'/promopackets',
        desc:'Возвращает список всех активных промо-пакетов провайдера. Промо-пакеты предоставляют бесплатный или льготный доступ к каналам по промо-ключу. Содержат: название, описание, срок действия и условия.',
        fields:[{n:'includes',l:'Includes'}] },
      { name:'getPacketById', label:'Промо по ID', http:'GET', endpoint:'/promopackets/{id}',
        desc:'Возвращает детальную информацию о конкретном промо-пакете: название, описание, состав каналов, период действия, количество доступных ключей.',
        fields:[{n:'packetId',l:'Packet ID',req:true,ph:'61'}] },
      { name:'getUserKeys', label:'Промо-ключи юзера', http:'GET', endpoint:'/users/{id}/promokeys',
        desc:'Возвращает список промо-ключей, активированных пользователем. Каждый ключ содержит: код, дату активации, срок действия, привязанный промо-пакет.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'activateUserKey', label:'Активировать ключ', http:'POST', endpoint:'/users/{id}/promokeys', danger:true,
        desc:'⚠️ Активирует промо-ключ для пользователя. Ключ предоставляет доступ к промо-пакету на определённый срок. Каждый ключ может быть активирован только один раз. Формат ключа: PROMO-XXXX.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'key',l:'Промо-ключ',req:true,ph:'PROMO-XXXX'}] },
      { name:'deactivateKey', label:'Деактивировать', http:'DELETE', endpoint:'/promokeys/{id}', danger:true,
        desc:'⚠️ Деактивирует промо-ключ по его ID. Пользователь теряет доступ к каналам, предоставленным этим промо-ключом. Используется при отмене промо-акции или обнаружении злоупотреблений.',
        fields:[{n:'keyId',l:'Key ID',req:true,ph:'123'}] },
    ]
  },
  messages: {
    label: 'Сообщения', icon: '✉️',
    methods: [
      { name:'getAll', label:'Все сообщения', http:'GET', endpoint:'/users/{id}/messages',
        desc:'Возвращает список всех сообщений, отправленных пользователю. Сообщения могут содержать уведомления о подписках, промо-акциях, техническом обслуживании. Отсортированы по дате создания.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'}] },
      { name:'getById', label:'Сообщение по ID', http:'GET', endpoint:'/users/{id}/messages/{messageId}',
        desc:'Возвращает конкретное сообщение по его ID. Содержит: заголовок, текст, дата создания, статус прочтения.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'messageId',l:'Message ID',req:true,ph:'1'}] },
      { name:'create', label:'Создать сообщение', http:'POST', endpoint:'/users/{id}/messages', danger:true,
        desc:'⚠️ Отправляет новое сообщение пользователю. Сообщение отображается в личном кабинете и на устройствах абонента. Используется для персональных уведомлений: информирование о задолженности, техработах, новых услугах.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'title',l:'Заголовок',ph:'Уведомление'},{n:'body',l:'Текст',ph:'Текст сообщения для абонента'}] },
      { name:'delete', label:'Удалить', http:'DELETE', endpoint:'/users/{id}/messages/{messageId}', danger:true,
        desc:'⚠️ Удаляет сообщение пользователя по ID. Удалённое сообщение больше не отображается в личном кабинете и на устройствах абонента. Восстановление невозможно.',
        fields:[{n:'userId',l:'User ID',req:true,ph:'12680'},{n:'messageId',l:'Message ID',req:true,ph:'1'}] },
    ]
  },
};

// ═══════════════════════════════════════════════════════════
//  STATE
// ═══════════════════════════════════════════════════════════

let state = {
  token: localStorage.getItem('24tv_token') || '',
  baseUrl: localStorage.getItem('24tv_baseUrl') || 'https://provapi.24h.tv/v2',
  activeService: null,
  history: JSON.parse(localStorage.getItem('24tv_history') || '[]'),
  isLoggedIn: false
};

// ═══════════════════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════════════════

function init() {
  if (state.token) {
    state.isLoggedIn = true;
    renderApp();
    selectService('dashboard');
  } else {
    renderLogin();
  }
}

// ═══════════════════════════════════════════════════════════
//  LOGIN
// ═══════════════════════════════════════════════════════════

function renderLogin() {
  document.getElementById('app-root').innerHTML = `
    <div class="login-screen">
      <div class="login-card fade-in">
        <h2>📺 24TV Control Panel</h2>
        <p class="sub">Центр управления API 24часаТВ — SDK v1.0.0</p>
        <form onsubmit="doLogin(event)">
          <div class="field">
            <label>Base URL API</label>
            <input id="login-url" type="text" value="${state.baseUrl}" placeholder="https://provapi.24h.tv/v2">
          </div>
          <div class="field">
            <label>API Token<span class="req">*</span></label>
            <input id="login-token" type="password" placeholder="Ваш API-токен провайдера" required autocomplete="off">
          </div>
          <div class="btn-row">
            <button type="submit" class="btn btn-primary" id="login-btn">Подключиться</button>
          </div>
          <p style="font-size:11px;color:hsl(var(--muted-fg));margin-top:12px">Токен сохраняется в localStorage браузера и используется только для запросов к API.</p>
        </form>
      </div>
    </div>`;
}

function doLogin(e) {
  e.preventDefault();
  state.baseUrl = document.getElementById('login-url').value.trim().replace(/\/+$/, '');
  state.token = document.getElementById('login-token').value.trim();
  if (!state.token) return;
  localStorage.setItem('24tv_token', state.token);
  localStorage.setItem('24tv_baseUrl', state.baseUrl);
  state.isLoggedIn = true;
  renderApp();
  selectService('dashboard');
}

function doLogout() {
  state.token = '';
  state.isLoggedIn = false;
  localStorage.removeItem('24tv_token');
  renderLogin();
}

// ═══════════════════════════════════════════════════════════
//  APP SHELL
// ═══════════════════════════════════════════════════════════

function renderApp() {
  document.getElementById('app-root').innerHTML = `
    <div class="app">
      <div class="header">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="hsl(217,91%,60%)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M17 2l-5 5-5-5"/></svg>
        <h1>24TV Panel</h1>
        <span class="logo">SDK v1.0.0</span>
        <div class="header-right">
          <span class="token-display" title="${state.baseUrl}">🔗 ${state.baseUrl.replace('https://','')}</span>
          <button class="btn-sm" onclick="toggleTheme()">
            <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
            <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
          </button>
          <button class="btn-sm" onclick="doLogout()" title="Выйти">🚪</button>
        </div>
      </div>
      <div class="sidebar" id="sidebar"></div>
      <div class="main" id="main"></div>
    </div>
    <button class="history-btn" onclick="toggleHistory()" title="История">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </button>
    <div class="history-panel" id="historyPanel">
      <div class="history-hdr">📜 История <button class="close" onclick="toggleHistory()">×</button></div>
      <div class="history-list" id="historyList"></div>
    </div>`;
  renderSidebar();
  renderHistory();
}

// ═══════════════════════════════════════════════════════════
//  SIDEBAR
// ═══════════════════════════════════════════════════════════

function renderSidebar() {
  const sb = document.getElementById('sidebar');
  let html = '<div class="sidebar-group">Навигация</div>';
  for (const [key, svc] of Object.entries(SERVICES)) {
    const active = state.activeService === key ? ' active' : '';
    const count = svc.methods ? svc.methods.length : '';
    html += `<div class="sidebar-item${active}" onclick="selectService('${key}')">
      <span>${svc.icon}</span> ${svc.label}
      ${count ? `<span class="badge">${count}</span>` : ''}
    </div>`;
  }
  html += '<div class="sidebar-bottom">';
  html += '<div class="sidebar-group">Инструменты</div>';
  html += `<div class="sidebar-item" onclick="toggleHistory()"><span>📜</span> История <span class="badge">${state.history.length}</span></div>`;
  html += `<div class="sidebar-item" onclick="showSettings()"><span>⚙️</span> Настройки</div>`;
  html += '</div>';
  sb.innerHTML = html;
}

function selectService(key) {
  state.activeService = key;
  renderSidebar();
  const svc = SERVICES[key];
  if (svc.isDashboard) renderDashboard();
  else renderMethods(key);
}

// ═══════════════════════════════════════════════════════════
//  DASHBOARD
// ═══════════════════════════════════════════════════════════

function renderDashboard() {
  document.getElementById('main').innerHTML = `
    <div class="fade-in">
      <h2 style="font-size:18px;font-weight:700;margin-bottom:16px">🔍 Быстрый поиск абонента</h2>
      <div class="quick-search">
        <input id="quick-input" type="text" placeholder="Введите ЛС, телефон, email или User ID..." onkeydown="if(event.key==='Enter')quickSearch()">
        <button class="btn btn-primary" onclick="quickSearch()">Найти</button>
      </div>
      <div id="quick-result"></div>
      <div class="dash-grid" style="margin-top:20px">
        <div class="dash-card"><div class="label">Версия SDK</div><div class="value" style="font-size:16px">1.0.0</div></div>
        <div class="dash-card"><div class="label">API Endpoint</div><div class="value" style="font-size:13px;word-break:break-all">${state.baseUrl}</div></div>
        <div class="dash-card"><div class="label">Сервисов</div><div class="value">${Object.keys(SERVICES).length - 1}</div></div>
        <div class="dash-card"><div class="label">Методов</div><div class="value">${Object.values(SERVICES).reduce((s,v)=>s+v.methods.length,0)}</div></div>
      </div>
    </div>`;
}

async function quickSearch() {
  const q = document.getElementById('quick-input').value.trim();
  if (!q) return;
  const div = document.getElementById('quick-result');
  div.innerHTML = '<span class="spinner"></span> Поиск...';
  // Определяем тип запроса
  let url, label;
  if (/^\d+$/.test(q) && q.length <= 6) {
    url = '/users?provider_uid=' + q; label = 'по ЛС';
  } else if (/^\d+$/.test(q)) {
    url = '/users/' + q; label = 'по ID';
  } else if (q.includes('@')) {
    url = '/users?email=' + encodeURIComponent(q); label = 'по email';
  } else if (/^[\d+]/.test(q)) {
    url = '/users?phone=' + q.replace(/\D/g,''); label = 'по телефону';
  } else {
    url = '/users?search=' + encodeURIComponent(q); label = 'поиск';
  }
  const res = await apiCall('GET', url);
  if (res.ok) {
    div.innerHTML = `<div class="response-panel"><div class="response-header"><span class="status-ok">✓ Найдено (${label})</span><span class="time">${res.time_ms} ms</span></div><div class="response-body">${syntaxHL(JSON.stringify(res.data,null,2))}</div></div>`;
  } else {
    div.innerHTML = `<div class="response-panel"><div class="response-header"><span class="status-err">✗ ${res.error || 'Ошибка'}</span><span class="time">${res.time_ms||0} ms</span></div><div class="response-body">${syntaxHL(JSON.stringify(res,null,2))}</div></div>`;
  }
}

// ═══════════════════════════════════════════════════════════
//  METHODS RENDERING
// ═══════════════════════════════════════════════════════════

function renderMethods(svcKey) {
  const svc = SERVICES[svcKey];
  let html = '<div class="methods-group fade-in">';
  svc.methods.forEach(m => {
    html += `<div class="method-item" data-state="closed" id="mc-${svcKey}-${m.name}">
      <div class="method-header" onclick="toggleMethod('${svcKey}','${m.name}')">
        <span class="method-chevron">${CHEVRON}</span>
        <span class="method-name">${m.label}</span>
        <span class="method-badge badge-${m.http}">${m.http}</span>
        <span class="method-endpoint">${m.endpoint}</span>
      </div>
      <div class="method-body"><div class="method-body-inner"><div class="method-content">
        ${m.desc ? `<div class="method-desc">${m.desc}</div>` : ''}
        ${m.danger ? '<div class="danger-warn">⚠️ Мутирующий метод — изменяет данные!</div>' : ''}
        <form onsubmit="execMethod(event,'${svcKey}','${m.name}')" id="form-${svcKey}-${m.name}">
          ${m.fields.map(f => renderField(svcKey,m.name,f)).join('')}
          <div class="btn-row">
            <button type="submit" class="btn ${m.danger?'btn-danger':'btn-primary'}" id="btn-${svcKey}-${m.name}">Отправить</button>
          </div>
        </form>
        <div id="resp-${svcKey}-${m.name}"></div>
      </div></div></div>
    </div>`;
  });
  html += '</div>';
  document.getElementById('main').innerHTML = html;
}

function renderField(svc, method, f) {
  const id = `${svc}-${method}-${f.n}`;
  const val = f.v || '';
  const ph = f.ph || '';
  if (f.type === 'json') {
    return `<div class="field"><label for="${id}">${f.l}${f.req?'<span class="req">*</span>':''}</label><textarea id="${id}" data-param="${f.n}" placeholder="${ph}" ${f.req?'required':''}>${val}</textarea></div>`;
  }
  return `<div class="field"><label for="${id}">${f.l}${f.req?'<span class="req">*</span>':''}</label><input id="${id}" data-param="${f.n}" type="text" value="${val}" placeholder="${ph}" ${f.req?'required':''} autocomplete="off"></div>`;
}

function toggleMethod(svc, method) {
  const el = document.getElementById(`mc-${svc}-${method}`);
  el.setAttribute('data-state', el.getAttribute('data-state') === 'open' ? 'closed' : 'open');
}

// ═══════════════════════════════════════════════════════════
//  API EXECUTION
// ═══════════════════════════════════════════════════════════

async function apiCall(method, url, body) {
  try {
    const res = await fetch(PROXY_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ url, method, token: state.token, baseUrl: state.baseUrl, body: body || null })
    });
    const text = await res.text();
    try { return JSON.parse(text); }
    catch(e) {
      const i = text.indexOf('{');
      if (i > 0) try { return JSON.parse(text.substring(i)); } catch(e2) {}
      return { ok: false, error: 'Invalid response', time_ms: 0 };
    }
  } catch(err) {
    return { ok: false, error: err.message, time_ms: 0 };
  }
}

async function execMethod(e, svcKey, methodName) {
  e.preventDefault();
  const form = document.getElementById(`form-${svcKey}-${methodName}`);
  const btn = document.getElementById(`btn-${svcKey}-${methodName}`);
  const respDiv = document.getElementById(`resp-${svcKey}-${methodName}`);
  const m = SERVICES[svcKey].methods.find(m => m.name === methodName);

  // Collect params
  const params = {};
  form.querySelectorAll('[data-param]').forEach(el => {
    const val = el.value.trim();
    if (val) {
      const field = m.fields.find(f => f.n === el.dataset.param);
      if (field && field.type === 'json') {
        try { params[el.dataset.param] = JSON.parse(val); } catch(ex) { params[el.dataset.param] = val; }
      } else if (val === 'true') params[el.dataset.param] = true;
      else if (val === 'false') params[el.dataset.param] = false;
      else params[el.dataset.param] = val;
    }
  });

  // Build URL
  let url = m.endpoint;
  // Replace path params
  if (params.userId) { url = url.replace('{id}', params.userId); }
  if (params.packetId) { url = url.replace('{packetId}', params.packetId).replace('{id}', params.packetId); }
  if (params.channelId) { url = url.replace('{id}', params.channelId); }
  if (params.subscriptionId) { url = url.replace('{subId}', params.subscriptionId); }
  if (params.deviceId) { url = url.replace('{deviceId}', params.deviceId); }
  if (params.tagId) { url = url.replace('{tagId}', params.tagId).replace('{id}', params.tagId); }
  if (params.pauseId) { url = url.replace('{pauseId}', params.pauseId); }
  if (params.messageId) { url = url.replace('{messageId}', params.messageId); }
  if (params.accountId) { url = url.replace('{accountId}', params.accountId); }
  if (params.licenseId) { url = url.replace('{licenseId}', params.licenseId); }
  if (params.keyId) { url = url.replace('{id}', params.keyId); }

  // Query params for GET
  let body = null;
  if (m.http === 'GET') {
    const qp = [];
    const pathKeys = ['userId','packetId','channelId','subscriptionId','deviceId','tagId','pauseId','messageId','accountId','licenseId','keyId'];
    for (const [k,v] of Object.entries(params)) {
      if (!pathKeys.includes(k) && v !== '' && v !== undefined) {
        qp.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
      }
    }
    if (qp.length) url += (url.includes('?') ? '&' : '?') + qp.join('&');
  } else {
    const pathKeys = ['userId','packetId','channelId','subscriptionId','deviceId','tagId','pauseId','messageId','accountId','licenseId','keyId'];
    body = {};
    for (const [k,v] of Object.entries(params)) {
      if (!pathKeys.includes(k)) body[k] = v;
    }
    if (Object.keys(body).length === 0) body = null;
  }

  // Confirm dangerous
  if (m.danger && !confirm(`⚠️ ${m.label}\n\nЭтот метод изменяет данные. Продолжить?`)) return;

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Запрос...';
  respDiv.innerHTML = '';

  const result = await apiCall(m.http, url, body);
  renderResponse(respDiv, result);
  addHistory(svcKey, methodName, params, result);

  btn.disabled = false;
  btn.textContent = 'Отправить';
}

// ═══════════════════════════════════════════════════════════
//  RESPONSE & JSON
// ═══════════════════════════════════════════════════════════

function renderResponse(el, data) {
  const ok = data.ok;
  const statusText = ok ? '<span class="status-ok">✓ Успешно</span>' : '<span class="status-err">✗ Ошибка</span>';
  const httpCode = data.http_code ? ` (HTTP ${data.http_code})` : '';
  const time = data.time_ms != null ? `${data.time_ms} ms` : '';
  const apiUrl = data.api_url ? `<span style="font-size:10px;color:hsl(var(--muted-fg));margin-left:8px">${data.api_url}</span>` : '';
  let body = ok ? data.data : (data.error ? { error: data.error, details: data.data } : data);
  el.innerHTML = `<div class="response-panel"><div class="response-header">${statusText}${httpCode}${apiUrl}<span class="time">${time}</span></div><div class="response-body">${syntaxHL(JSON.stringify(body,null,2))}</div></div>`;
}

function syntaxHL(json) {
  if (!json) return '';
  json = json.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  return json.replace(/"(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, function(m) {
    let c = 'json-num';
    if (/^"/.test(m)) c = /:$/.test(m) ? 'json-key' : 'json-str';
    else if (/true|false/.test(m)) c = 'json-bool';
    else if (/null/.test(m)) c = 'json-null';
    return '<span class="'+c+'">'+m+'</span>';
  });
}

// ═══════════════════════════════════════════════════════════
//  HISTORY
// ═══════════════════════════════════════════════════════════

function addHistory(svc, method, params, result) {
  state.history.unshift({ svc, method, params, ok: result.ok, time_ms: result.time_ms, ts: new Date().toISOString() });
  if (state.history.length > 50) state.history = state.history.slice(0, 50);
  localStorage.setItem('24tv_history', JSON.stringify(state.history));
  renderHistory();
  renderSidebar();
}

function renderHistory() {
  const list = document.getElementById('historyList');
  if (!list) return;
  if (!state.history.length) { list.innerHTML = '<div class="empty-state" style="padding:30px"><p>Пока нет запросов</p></div>'; return; }
  list.innerHTML = state.history.map((h,i) => {
    const svcLabel = SERVICES[h.svc] ? SERVICES[h.svc].label : h.svc;
    const time = new Date(h.ts).toLocaleTimeString('ru-RU');
    const cls = h.ok ? 'status-ok' : 'status-err';
    const txt = h.ok ? '✓' : '✗';
    return `<div class="history-item" onclick="replayHistory(${i})"><div class="hi-method"><span class="${cls}">${txt}</span> ${svcLabel} → ${h.method}</div><div class="hi-time">${time} · ${h.time_ms||0} ms</div></div>`;
  }).join('');
}

function replayHistory(i) {
  const h = state.history[i];
  if (!SERVICES[h.svc]) return;
  selectService(h.svc);
  setTimeout(() => {
    const card = document.getElementById(`mc-${h.svc}-${h.method}`);
    if (card) {
      card.setAttribute('data-state', 'open');
      card.scrollIntoView({ behavior: 'smooth', block: 'center' });
      const form = document.getElementById(`form-${h.svc}-${h.method}`);
      if (form && h.params) {
        for (const [k,v] of Object.entries(h.params)) {
          const el = form.querySelector(`[data-param="${k}"]`);
          if (el) el.value = typeof v === 'object' ? JSON.stringify(v) : String(v);
        }
      }
    }
    toggleHistory();
  }, 100);
}

function toggleHistory() {
  document.getElementById('historyPanel').classList.toggle('open');
}

// ═══════════════════════════════════════════════════════════
//  SETTINGS
// ═══════════════════════════════════════════════════════════

function showSettings() {
  state.activeService = null;
  renderSidebar();
  document.getElementById('main').innerHTML = `
    <div class="fade-in">
      <h2 style="font-size:18px;font-weight:700;margin-bottom:16px">⚙️ Настройки</h2>
      <div style="background:hsl(var(--card));border:1px solid hsl(var(--border));border-radius:var(--radius);padding:20px;max-width:500px">
        <div class="field"><label>Base URL</label><input id="set-url" type="text" value="${state.baseUrl}"></div>
        <div class="field"><label>API Token</label><input id="set-token" type="password" value="${state.token}"></div>
        <div class="btn-row">
          <button class="btn btn-primary" onclick="saveSettings()">Сохранить</button>
          <button class="btn btn-sm" onclick="clearHistory()">🗑 Очистить историю</button>
        </div>
      </div>
    </div>`;
}

function saveSettings() {
  state.baseUrl = document.getElementById('set-url').value.trim().replace(/\/+$/, '');
  state.token = document.getElementById('set-token').value.trim();
  localStorage.setItem('24tv_baseUrl', state.baseUrl);
  localStorage.setItem('24tv_token', state.token);
  alert('Настройки сохранены');
}

function clearHistory() {
  state.history = [];
  localStorage.removeItem('24tv_history');
  renderHistory();
  renderSidebar();
  alert('История очищена');
}

// ═══════════════════════════════════════════════════════════
//  THEME
// ═══════════════════════════════════════════════════════════

function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
}

// ═══════════════════════════════════════════════════════════
//  BOOT
// ═══════════════════════════════════════════════════════════

(function() {
  var t = localStorage.getItem('theme');
  if (!t) t = window.matchMedia('(prefers-color-scheme:light)').matches ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', t);
})();

document.addEventListener('DOMContentLoaded', init);
