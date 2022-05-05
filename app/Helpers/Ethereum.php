<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Web3;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Providers\HttpProvider;

class Ethereum {
    protected static $urls_dev = [
        [
            'url' => "https://kovan.infura.io/v3/196c0913fba34d73bd4bf7ae84512191",
            'weight' => 1,
        ],
    ];
    protected static $urls_production = [
        [
            'url' => "https://mainnet.infura.io/v3/196c0913fba34d73bd4bf7ae84512191",
            'weight' => 1,
        ],
    ];
    protected $username = '';
    protected $password = '';
    
    const GAS_LIMIT = 90000;
    
    protected $last_error = [];
    protected $error_as_message = false;
    
    
    const ERC20_ABI = <<<EOF
[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_value","type":"uint256"}],"name":"approve","outputs":[{"name":"success","type":"bool"}],"type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"success","type":"bool"}],"type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"type":"function"},{"constant":true,"inputs":[],"name":"version","outputs":[{"name":"","type":"string"}],"type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"balance","type":"uint256"}],"type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transfer","outputs":[{"name":"success","type":"bool"}],"type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_value","type":"uint256"},{"name":"_extraData","type":"bytes"}],"name":"approveAndCall","outputs":[{"name":"success","type":"bool"}],"type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_spender","type":"address"}],"name":"allowance","outputs":[{"name":"remaining","type":"uint256"}],"type":"function"},{"inputs":[{"name":"_initialAmount","type":"uint256"},{"name":"_tokenName","type":"string"},{"name":"_decimalUnits","type":"uint8"},{"name":"_tokenSymbol","type":"string"}],"type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_from","type":"address"},{"indexed":true,"name":"_to","type":"address"},{"indexed":false,"name":"_value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_owner","type":"address"},{"indexed":true,"name":"_spender","type":"address"},{"indexed":false,"name":"_value","type":"uint256"}],"name":"Approval","type":"event"}]
EOF;


    /// 获取一个地址拥有的 token 余额
    /// 返回的 token 余额的小数点已经经过 decimals 处理
    static function getTokenBalance($token_address, $wallet_address) {
        return self::getERC20Balance($token_address, $wallet_address);
    }

    /// 获取一个 token 的信息
    static function getToken($token_address) {
        return self::getERC20Info($token_address);
    }


    static function LOGD($log, ...$args) {
        if (!empty($args)) {
            $log = vsprintf($log, $args);
        }
        Log::debug($log);
    }
    
    /**
     * 将某个指定的 url 设定为失败
     */
    public static function setRPCURLFail($url) {
        $ts = time();
        $ttl = 600;         /// 10 分钟后认为 URL 恢复正常
        self::LOGD("将以太坊 RPC URL （{$url}）设为不可用，当前时间是 $ts");
        \apcu_store("ethereum_rpc_url_fail_time_{$url}", $ts, 600);
    }
    
    /**
     * 返回一个可用的 URL 列表（根据权重以及最后访问时间排序）
     */
    public static function getRPCURLs($method = '') {
        $urls = self::$urls_dev;

        if (env('APP_ENV') == 'production') {
            $urls = self::$urls_production;
        }
        
        
        
        $fail_times = [];
        foreach ($urls as $url) {
            $fail_times[] = (int)\apcu_fetch("ethereum_rpc_url_fail_time_{$url['url']}");
            $sort_weights[] = lcg_value() * $url['weight'];
        }
        
        self::LOGD("准备进行排序，计算出来的 weights 和 fail_times 分别是: %s, %s", json_encode($sort_weights), json_encode($fail_times));
        
        //array_multisort($sort_weights, SORT_DESC, $fail_times, SORT_ASC, $urls);
        array_multisort($fail_times, SORT_ASC, $sort_weights, SORT_DESC, $urls);
        
        return $urls;
    }
    
    
    /**
     * 向比特币程序发起一次 RPC 请求
     * 
     * @param method    命令名字，如 getblockchaininfo
     * @param params    要传递的参数列表，以数组形式提供
     */
    public static function rpc($method, $params = [], &$err = '') {
        static $rpc_streak_fail_key = "ethereum_rpc_streak_fail_count";
        
        if (!is_array($params)) {
            $params = [$params];
        }
        
        $data = json_encode([
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => (int)hrtime(true),
        ]);
        
        
        $raw = false;
        $urls = self::getRPCURLs($method);
        
        foreach ($urls as $k => $url) {
            self::LOGD("开始进行第 `%d' 次以太坊 RPC 请求(%s)", $k + 1, $url['url']);
            
            $timeout = 5;
            if ($k > 0) {
                $timeout = 20;
            }
            
            //raw = Ext_Http::sendRequest($url['url'], $data, 'POST', 60, $this->username, $this->password, 'application/json');
            // $param = [
            //     'user' => $this->username,
            //     'password' => $this->password,
            //     'content_type' => 'application/json',
            //     'timeout' => $timeout,
            //     'method' => 'post',
            //     'body' => $data,
            // ];
            // $opt = [
            //     'ignore_timeout_warning',
            //     'no_print_result',
            //     'warning_as_notice',
            // ];
            // $raw = Ext_Http::request($url['url'], $param, $opt);
            

            $c = new \GuzzleHttp\Client();
            $raw = $c->request('post', $url['url'], [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $data,
            ])->getBody();
            
            if (!$raw) {
                self::LOGD("由于请求第 %d 个以太坊 RPC 接口(%s)失败，将尝试下一个接口（如果有的话）", $k + 1, $url['url']);
            }
            else {
                break;
            }
        }
        
        
        
        $j = NULL;
        if ($raw) {
            $j = json_decode($raw, TRUE);
            
            if (!$j) {
                $fail_count = 'unavail';
                
                self::LOGD("无法将以太坊客户端返回的数据解析为 JSON，已经连续失败 {$fail_count} 次，原始返回是：" . var_export($raw, TRUE));
                
                $err = __('以太坊后端返回了无法解析的数据');
                return FALSE;
            }
            else if (isset($j['error']) && $j['error']) {
                self::LOGD("以太坊客户端 rpc 明确返回了错误信息: " . var_export($j['error'], TRUE));
                $err = $j['error']['message'] ?? '';
                return FALSE;
            }
            
            
        }
        else {
            self::LOGD("以太坊后端 rpc 请求失败，请求命令是 %s, 请求参数是 %s, 最后一次请求的原始返回结果是: %s", $method, json_encode($params), var_export($raw, TRUE));
                       
            $err = __('以太坊后端没有响应');
            return $raw;
        }
        
        if (isset($j['message'])) {
            $err = $j['message'];
        }
        if (isset($j['data']) && is_string($j['data'])) {
            $err .= '.' . $j['data'];
        }
        
        return $j['result'];
    }
    
    
    public function getBlockChainInfo() {
        return $this->rpc('getblockchaininfo');
    }
    
    /**
     * 获取一个区块的信息
     * 
     * @param no        区块高度，十进制格式，也可以传入以 0x 开头的十六进制格式
     * @param with_tx   是否同时返回 tx 数据
     */
    public function getBlock($no, $with_tx = TRUE) {
        if (!$no) {
            self::LOGD("传入的 no 参数值 `%s' 不正确", $no);
            return false;
        }
        
        
        if (substr($no, 0, 2) != '0x') {
            $no = gmp_init($no, 10);
            
            if ($no === false) {
                $this->setError("无法解析传入的 no: " . var_export($no, true));
            }
            
            $no = gmp_strval($no, 16);
            $no = '0x' . $no;
        }
        
        
        $key = sprintf("cache_ethereum_block_{$no}_%s", $no, $with_tx ? 'true' : 'false');
        
        /// 1. 尝试从缓存中获取
        // $t1 = microtime(true);
        // $result = Ext_Cache_Redis::getInstance()->get($key);
        
        // if ($result) {
        //     self::LOGD("从缓存中获取到了 ETH getBlock() `%s' 的结果，耗时 %0.3f 毫秒", $no, (microtime(true) - $t1) * 1000);
        //     return $result;
        // }
        
        
        /// 2. 从 ETH 网络进行查询
        $result = $this->rpc('eth_getBlockByNumber', [$no, $with_tx]);
        
        if (!$result || !isset($result['hash'])) {
            self::LOGD("无法获取高度为 `{$no}' 的以太币区块");
            return FALSE;
        }
        
        
        /// 3. 缓存查询结果
        self::LOGD("将 ethereum getBlock() 的结果(no=%s, with_tx=%s) 缓存起来", $no, $with_tx);
//        Ext_Cache_Redis::getInstance()->set($key, $result, 3600);
        
        return $result;
    }
    
    
    public function getTx($hash) {
        $hash = strtolower($hash);
        $key = "cache_ethereum_tx_{$hash}";
        
        /// 1. 尝试从缓存中获取
        // $t1 = microtime(true);
        // $result = Ext_Cache_Redis::getInstance()->get($key);
        
        // if ($result) {
        //     self::LOGD("从缓存中获取 ETH tx `%s' 的结果，耗时 %0.3f 毫秒", $hash, (microtime(true) - $t1) * 1000);
        //     return $result;
        // }
        
        /// 2. 从 ETH 获取
        $result = $this->rpc('eth_getTransactionByHash', [$hash]);
        
        if ($result) {
//            Ext_Cache_Redis::getInstance()->set($key, $result, 3600);
        }
        else {
            return null;
        }
        
        return $result;
    }
    

    /**
     * 获取一个交易的 receipt
     * 
     * @return mixed        返回 NULL 表示网络请求失败，无法确认交易是否有 receipt。FALSE 表示没有此交易 receipt
     */
    public function getTxReceipt($hash) {
        $hash = strtolower($hash);
        $key = "cache_ethereum_tx_receipt_{$hash}";
        
        /// 1. 尝试从缓存中获取
        // $t1 = microtime(true);
        // $result = Ext_Cache_Redis::getInstance()->get($key);
        
        // if ($result) {
        //     self::LOGD("从缓存中获取 ETH tx receipt `%s' 的结果，耗时 %0.3f 毫秒", $hash, (microtime(true) - $t1) * 1000);
        //     return $result;
        // }
        
        /// 2. 从 ETH 网络获取
        $result = $this->rpc('eth_getTransactionReceipt', [$hash]);
        
        if ($result) {
//            Ext_Cache_Redis::getInstance()->set($key, $result, 3600);
        }
        else {
            return null;
        }
        
        return $result;
    }
    
    
    /**
     * 获取最后一个块
     * 
     * @param with_tx       是否同时返回 tx 数据
     */
    public function getLastBlock($with_tx = FALSE) {
        /// 1. 尝试从缓存查询
        $key = "cache_ethereum_last_block_number";
        $result = null;

        $t1 = microtime(true);
        // $result = Ext_Cache_Redis::getInstance()->get($key);
        
        // if ($result) {
        //     self::LOGD("从缓存中获取 ETH getLastBlock()  的结果，耗时 %0.3f 毫秒", (microtime(true) - $t1) * 1000);
        // }
        
        /// 2. 尝试从 ETH 网络查询
        if (!$result) {
            $result = $this->rpc('eth_blockNumber');
        }
        
        if (!$result) {
            self::LOGD("无法获取最后一个 eth 块的高度");
            return FALSE;
        }
        
        
        
        /// 3. 将数据缓存起来
//        Ext_Cache_Redis::getInstance()->set($key, $result, 10); /// ETH 平均出块时间是 15s，我们将缓存设为 10s
       
       
        /// 4. 查询这个块的数据并返回
       $block = $this->getBlock($result, $with_tx);
       
       return $block;
    }
    
    public function getLastBlockNumber() {
        $block = $this->getLastBlock();
        return $this->hex2dec($block['number']);
    }
    
    
    /**
     * 将一个以太币的 hash 转换成十进制
     * 
     * @param hex       以太币的 hash, 可以带 0x 前缀，也可以不带 0x 前缀
     */
    public static function hex2dec($hex) {
        if (strpos($hex, '0x') === 0) {
            $hex = substr($hex, 2);
        }
        
        $hex = strtolower($hex);
        
        if ($hex === '' || $hex === null || $hex === false) {
            self::LOGD("传入的数值 `{$hex}' 不是一个合法的数字，无法初始化 GMP");
            return false;
        }
        
        $ret = gmp_init($hex, 16);
        if (!is_object($ret)) {
            //self::LOGD("传入的数值 `{$hex}' 不是一个合法的数字，无法初始化 GMP");
            return false;
        }
        
        $ret = gmp_strval($ret, 10);
        
        return $ret;
    }
    
    static public function dec2hex($dec) {
        return gmp_strval(gmp_init($dec, 10), 16);
    }

    static public function dec20xhex($dec) {
        $val = self::dec2hex($dec);
        if (strlen($val) % 2 != 0) {
            $val = "0" . $val;
        }
        
        return "0x" . $val;
    }
    
    /**
     * 将一个 wei 为单位的以太币数值转换成 ether 为单位的以太币数值。输入和输出均为十进制
     */
    public static function wei2ether($wei) {
        $b = gmp_init($wei, 10);

        $b = gmp_strval($b);
        $b = bcdiv($b, '1000000000000000000', 20);
        $b = rtrim($b, '0');
        if (substr($b, -1, 1) == '.') {
            $b .= '0';
        }
        
        return $b;
    }
    
    /**
     * 将一个 eth 为单位的以太币数值转换成以 weei 为数值的以太币单位。输入和输出均为十进制
     */
    public function ether2wei($ether) {
        bcscale(20);
        
        $wei = bcmul($ether, bcpow(10, 18));
        $wei = explode('.', $wei);
        $wei = $wei[0];
        
        return $wei;
    }
    
    /**
     * 将两个以 0x 开头的十六进制数字相乘，结果以 0x 开头的十六进制返回
     */
    public function hexMul($a, $b) {
        if (substr($a, 0, 2) != '0x') {
            self::LOGD("输入的第一个参数不正确`{$a}'，必须以 0x 开头");
        }
        else {
            $a = substr($a, 2);
        }
        
        if (substr($b, 0, 2) != '0x') {
            self::LOGD("输入的第二个参数不正确`{$b}'，必须以 0x 开头");
        }
        else {
            $b = substr($b, 2);
        }
        
        $a = gmp_init($a, 16);
        $b = gmp_init($b, 16);
        $c = gmp_mul($a, $b);
        
        return '0x' . gmp_strval($c, 16);
    }
    
    /**
     * 将两个以 0x 开头的十六进制数字相减，结果以 0x 开头的十六进制返回
     */
    public function hexSub($a, $b) {
        if (substr($a, 0, 2) != '0x') {
            self::LOGD("输入的第一个参数不正确`{$a}'，必须以 0x 开头");
        }
        else {
            $a = substr($a, 2);
        }
        
        if (substr($b, 0, 2) != '0x') {
            self::LOGD("输入的第二个参数不正确`{$b}'，必须以 0x 开头");
        }
        else {
            $b = substr($b, 2);
        }
        
        $a = gmp_init($a, 16);
        $b = gmp_init($b, 16);
        $c = gmp_sub($a, $b);
        
        return '0x' . gmp_strval($c, 16);
    }
    
    /**
     * 比较两个以 0x 开头的十六进制数字的大小
     * 
     * @return int      当 $a > $b 时返回 1，当 $a < $b 时返回 -1，当相等时返回 0
     */
    public function hexCmp($a, $b) {
        if (substr($a, 0, 2) != '0x') {
            self::LOGD("输入的第一个参数不正确`{$a}'，必须以 0x 开头");
        }
        else {
            $a = substr($a, 2);
        }
        
        if (substr($b, 0, 2) != '0x') {
            self::LOGD("输入的第二个参数不正确`{$b}'，必须以 0x 开头");
        }
        else {
            $b = substr($b, 2);
        }
        
        $a = gmp_init($a, 16);
        $b = gmp_init($b, 16);
        
        return gmp_cmp($a, $b);
    }
    
    
    
    /**
     * 传入一个 block，获取这个 block 的确认数量
     */
    public function getBlockConfirmCount($block) {
        $last_block = $this->getLastBlock();
        if (!$last_block) {
            self::LOGD("无法获得最后一个 eth block 的信息");
            return NULL;
        }
        
        if ($block['number'] === null) {
            self::LOGD("指定的 block 还没有 number 数据，直接返回 0");
            return 0;
        }
        
        $block_number = $this->hex2dec($block['number']);
        $last_block_number = $this->hex2dec($last_block['number']);
        
        $confirm_count = $last_block_number - $block_number +1;
        return max($confirm_count, 0);
    }
    
    /**
     * 传入一个 tx，获取这个 tx 的确认数量
     * 
     * @param tx        可以是 tx_id，也可以是一个 tx 数据数组
     */
    public function getTxConfirmCount($tx) {
        if (is_string($tx)) {
            $tx_id = $tx;
            
            $tx = $this->getTx($tx_id);
            if (!$tx) {
                self::LOGD("无法获得 `{$tx_id}' 交易的数据，直接返回 null");
                return null;
            }
        }
        
        if ($tx['blockNumber'] === null) {
            self::LOGD("以太币 tx({$tx['hash']})) 还没有被包含到一个块上，直接返回 0 作为确认数");
            return 0;
        }
        
        $block = $this->getBlock($tx['blockNumber']);
        if (!$block) {
            self::LOGD("无法获得 tx({$tx['hash']}) 对应的 block({$tx['blockNumber']})");
            return NULL;
        }
        
        return $this->getBlockConfirmCount($block);
    }
    
    
    /**
     * 传入一个地址，获取这个地址的余额
     * 
     * @param address           以太币地址
     * @param return_readable   是否以十进制格式返回便于阅读的 ETH 余额
     * @return balance          如果返回十进制格式的余额，单位为 ETH，如果返回原始数据，则返回 0x 开头的十六进制字符串，单位为 wei
     */
    public static function getBalance_deprecated($address, $return_readable = true) {
        $result = self::rpc('eth_getBalance', [$address]);
        
        if ($result === FALSE) {
            return false;
        }
        
        
        $balance = $result;
        if ($return_readable) {
            $balance = self::hex2dec($balance);
            $balance = self::wei2ether($balance);
        }
        
        return $balance;
    }
    
    
    /**
     * 传入一个地址，获取这个地址的余额
     * 
     * @param address           以太币地址
     * @param return_readable   是否以十进制格式返回便于阅读的 ETH 余额
     * @return balance          如果返回十进制格式的余额，单位为 ETH，如果返回原始数据，则返回 0x 开头的十六进制字符串，单位为 wei
     */
    static public function getBalance($address) {
        //$result = Ext_Http::request("http://10.26.0.45:5300/address/{$address}/balance");

        $result = self::rpc('eth_getBalance', [$address, 'latest']);
        if ($result === false) {
            return false;
        }

        $balance = self::hex2dec($result);
        $balance = bcdiv($balance, bcpow(10, 18), 18);
        
        return Bc::trimr0($balance);
    }
    
    
    
    
    /**
     * 获取当前的 gas 价格，以 0x 开头的十六进制格式返回
     */
    public function getGasPrice() {
        $result = $this->rpc('eth_gasPrice', []);
        
        if ($result === FALSE) {
            return false;
        }
        
        return $result;
    }
    
    
    /**
     * 获取一个指定的 tx 的 trace
     * 
     * @param tx_hash       指定的交易的 hash
     */
    public function getTrace($tx_hash) {
        $tx_hash = strtolower($tx_hash);
        
        $key = "cache_ethereum_trace_{$tx_hash}";
        
        
        /// 1. 尝试从缓存查询结果
        // $t1 = microtime(true);
        // $result = Ext_Cache_Redis::getInstance()->get($key);
        // if ($result) {
        //     self::LOGD("从缓存中获取 ETH tx `%s' 的结果，耗时 %0.3f 毫秒", $tx_hash, (microtime(true) - $t1) * 1000);
        //     return $result;
        // }
        
        
        /// 2. 从客户端查询结果
        $result = $this->rpc('trace_transaction', [$tx_hash]);
        
        if ($result === false) {
            return false;
        }
        else {
            self::LOGD("将 ethereum getTrace() 的结果（{$tx_hash}）缓存起来");
//            Ext_Cache_Redis::getInstance()->set($key, $result, 3600);
        }
        
        
        return $result;
    }
    
    /**
     * 获取一个 tx 中具有指定 trace_address 的 trace
     */
    public function getTraceWithTraceAddress($tx_hash, $trace_address) {
        $traces = $this->getTrace($tx_hash);
        
        if (!is_array($traces)) {
            self::LOGD("无法获取 `{$tx_hash}' 的 trace 数据，无法查询 trace，获得的原始数据是: " . var_export($traces, true));
            return false;
        }
        
        foreach ($traces as $t) {
            if (implode('/', $t['traceAddress']) != $trace_address) {
                continue;
            }
            else {
                $trace = $t;
                break;
            }
        }
        
        if (!$trace) {
            self::LOGD("找不到交易 `{$tx_hash}' 的 traceAddress 为 `{$trace_address}' 的 trace，无法处理此请求，返回 null");
            return null;
        }
        
        return $trace;
    }
    
    
    /**
     * @param data.to       收款地址
     * @param data.data     数据内容
     */
    public function getEstimateGas($data) {
        /// 自动处理 gas_price --> gasPrice 之类的命名
        
        if (isset($data['gas_price'])) {
            $data['gasPrice'] = $data['gas_price'];
            unset($data['gas_price']);
        }
        
        if (isset($data['private_key'])) {
            unset($data['private_key']);
        }
        if (isset($data['gas'])) {
            unset($data['gas']);
        }
        if (isset($data['nonce'])) {
            unset($data['nonce']);
        }
        
        
        $err = '';
        $result = $this->rpc('eth_estimateGas', [$data], $err);
        
        if ($err) {
            $this->setErrorAsMessage("eth_estimateGas 调用失败: " . $err);
        }
        
        if ($result === false) {
            return false;
        }
        
        return $result;
    }
    
    /**
     * 获取一个账户的 next nonce
     * 
     * @return string       以 0x 开头的十六进制格式
     */
    public function getNextNonce_deprecated($address) {
        $err = '';
        $result = $this->rpc('parity_nextNonce', [$address], $err);
        
        if ($err) {
            $this->setMessage($err);
        }
        
        if ($result === false) {
            return false;
        }
        else {
            return $result;
        }
    }
    
    /**
     * 获取一个账户的 next nonce
     * 
     * @return string       以 0x 开头的十六进制格式
     */
    public function getNextNonce($address) {
        //$result = Ext_Http::request("http://10.26.0.45:5300/address/{$address}/nonce");
        $result = (new \GuzzleHttp\Client())->request('get', "http://10.26.0.45:5300/address/{$address}/nonce")->getBody();

        $j = json_decode($result, true);
        
        if ($j['code'] != 0) {
            return false;
        }
        
        $next_nonce = $j['data']['next_nonce'];
        
        return sprintf("0x%x", $next_nonce);
    }
    
    
    /**
     * 获取一个 erc20 代币合约上地址的余额
     * 
     * @param contract_hash     合约地址，以 0x 开头
     * @param address           地址，以 0x 开头
     * 
     * @return string           返回指定地址在指定合约上的余额，格式为十进制
     */
    public function ERC20BalanceOf($contract_hash, $address) {
        $urls = $this->getRPCURLs('no_tracing');
        
        $ret = null;
        
        foreach ($urls as $u) {
            $ret = $this->ERC20BalanceOf_internal($u['url'], $contract_hash, $address);
            if (!$ret) {
                $this->setRPCURLFail($u['url']);
                break;
            }
        }
        
        return $ret;
    }
    
    public function ERC20BalanceOf_internal($url, $contract_hash, $address) {
        $ret = null;
        
        try {
            $contract = new Web3\Contract($url, self::ERC20_ABI);
            
            $functionData = $contract->at($contract_hash)->call('balanceOf', $address, function($a, $b) use (&$ret, $contract_hash) {
                if (isset($b['balance'])) {
                    $ret = (string)$b['balance'];
                }
                else {
                    self::LOGD("合约 `{$contract_hash}' 的 balacneOf 调用返回了无法解析的数据: %s", var_export(func_get_args(), true));
                }
            });
        }
        catch (\Exception $e) {
            self::LOGD("进行 ERC20 合约调用时发生异常: %s", $e->getMessage());
        }
        
        return $ret;
    }
    
    
    /**
     * 获取一个 ERC20 代币的信息
     * 
     * @param contract_hash     ERC20 代币的地址，以 0x 开头
     * @return array            返回 ERC20 代币的信息，包括的内容有: [ name, totalSupply => 十进制, decimals => 十进制, version, symbol ]
     */
    public static function getERC20Info($contract_hash) {
        $urls = self::getRPCURLs('no_tracing');
        
        $ret = null;
        
        
        foreach ($urls as $u) {
            $ret = self::getERC20Info_internal($u['url'], $contract_hash);
            
            if (!$ret) {
                
                break;
            }
        }
        
        if (is_array($ret)) {
            if (!isset($ret['decimals']) || $ret['decimals'] <= 0) {
                if ($ret['name'] === 'Dentacoin' && $ret['decimals'] === '0') {
                    /// Dentacoin 的小数点位数是 0，什么都不用做
                } else {
                    self::LOGD("ERC20 货币（{$contract_hash}）目前获取到的小数点位数是非法的，拒绝返回此货币的 ERC20 信息. " . json_encode($ret));
                    return null;
                }
            }
        }
        
        return $ret;
    }
    
    public static function getERC20Info_internal($url, $contract_hash) {
        static $erc20s = [];
        
        $ret = [];
        
        if (!Web3\Utils::isAddress($contract_hash)) {
            self::LOGD("传入的 contract_hash `{$contract_hash}' 不是一个合法的地址");
            return null;
        }
        $contract_hash = strtolower($contract_hash);
        
        if (isset($erc20s[$contract_hash])) {
            return $erc20s[$contract_hash];
        }
        
        
        $key = "eth_erc20_info_{$contract_hash}";

        // $ret = Ext_Cache_Redis::getInstance()->get($key);
        // if ($ret) {
        //     self::LOGD("从缓存中查询到了 ERC20 代币 `{$contract_hash}' 的信息");
            
        //     $erc20s[$contract_hash] = $ret;
            
        //     return $ret;
        // }
        $ret = Cache::get($key, null);
        if ($ret) {
            self::LOGD("从缓存中查询到了 ERC20 代币 `{$contract_hash}' 的信息");
            $erc20s[$contract_hash] = $ret;
            return $ret;
        }

        
        
        try {
            //$contract = new Web3\Contract($url, self::ERC20_ABI);
            $contract = new Web3\Contract(new HttpProvider(new HttpRequestManager($url, 10)), self::ERC20_ABI);
            
            /// 获取名字
            do {
                $functionData = $contract->at($contract_hash)->call('name', function($a, $b) use (&$ret, $contract_hash) {
                    if (!is_array($b)) {
                        self::LOGD("无法解析 ERC20 智能合约`{$contract_hash}'的名字（使用小写名字 name）");
                        return;
                    }
                    
                    $ret['name'] = (string)current($b);
                });
                
                /*
                if (!isset($ret['name'])) {
                $functionData = $contract->at($contract_hash)->call('NAME', function($a, $b) use (&$ret, $contract_hash) {
                    if (!is_array($b)) {
                        self::LOGD("无法解析 ERC20 智能合约`{$contract_hash}'的名字（使用大写名字 NAME）");
                        return ;
                    }
                    
                    $ret['name'] = (string)current($b);
                });
                */
                
                if (!isset($ret['name'])) {
//                    $fail_count = Ext_Misc::incrStreakCount("get_erc20_info_fail_{$contract_hash}");
                    
                    self::LOGD("由于无法获取智能合约 `%s' 的名字，这个智能合约的数据可能是不可用的，不会再尝试读取此智能合约的数据，返回的原始数据是: %s", $contract_hash, json_encode($ret));
                     
                    
                    break;
                } else {
//                    Ext_Misc::resetStreakCount("get_erc20_info_fail_{$contract_hash}");
                }
                
                
                /// 获取缩写
                $functionData = $contract->at($contract_hash)->call('symbol', function($a, $b) use (&$ret) {
                    $ret['symbol'] = (string)current($b);
                });


                /// 获取小数位数
                $functionData = $contract->at($contract_hash)->call('decimals', function($a, $b) use (&$ret, $contract_hash) {
                    if (!is_array($b)) {
                        self::LOGD("无法获取到智能合约 `{$contract_hash}' 的 decimals 属性");
                        return;
                    }
                        
                    $ret['decimals'] = (string)current($b);
                });
                
                /*
                if (!isset($ret['decimals'])) {
                    $functionData = $contract->at($contract_hash)->call('DECIMALS', function($a, $b) use (&$ret) {
                        if (!is_array($b)) {
                            self::LOGD("无法获取到智能合约 `{$contract_hash}' 的 DECIMALS 属性");
                            return;
                        }
                        
                        $ret['decimals'] = (string)current($b);
                    });
                }
                */
                
                
                /// 获取供应总量
                $functionData = $contract->at($contract_hash)->call('totalSupply', function($a, $b) use (&$ret) {
                    if (is_array($b)) {
                        $ret['totalSupply'] = (string)current($b);
                    }
                    else {
                        $ret['totalSupply'] = -1;
                    }
                });
                
                /// 获取版本
                $functionData = $contract->at($contract_hash)->call('version', function($a, $b) use (&$ret) {
                    if (is_array($b)) {
                        $ret['version'] = (string)current($b);
                    }
                    else {
                        $ret['version'] = 'null (by coinchat)';
                    }
                });
            }
            while (0);
        }
        catch (\Exception $e) {
            self::LOGD("进行 ERC20 合约调用时发生异常，目前已经获取的信息有 %s.  %s", json_encode($ret), $e->getMessage());
            return null;
        }
        
        if (!$ret || !isset($ret['decimals']) || $ret['decimals'] <= 0) {
            if ($ret['name'] === 'Dentacoin' && $ret['decimals'] === '0') {
                /// Dentacoin 的小数点位数是 0，什么都不用做
            } else {
                self::LOGD("获取到的 ERC20({$contract_hash}) 的小数点信息是非法的（或未能获取到 ERC20 信息），丢弃此 ERC20 信息. " . json_encode($ret));
                $ret = null;
            }
        }
        
        
        if (!empty($ret) && $ret['name']) {
//            Ext_Cache_Redis::getInstance()->set($key, $ret);
            Cache::put($key, $ret, 86400);
            self::LOGD("将 ERC20 代币 `{$contract_hash}' 的信息设置到了缓存中: %s", json_encode($ret));
        }
        
        
        $erc20s[$contract_hash] = $ret;
        
        return $ret;
    }
    
    
    public static function getERC20Balance($contract_hash, $address) {
        $urls = self::getRPCURLs('no_tracing');
        
        $ret = null;
        
        foreach ($urls as $u) {
            $ret = self::getERC20Balance_internal($u['url'], $contract_hash, $address);
            
            if (!$ret) {
                self::setRPCURLFail($u['url']);
                continue;
            } else {
                break;
            }
        }
        
        return $ret;
    }
    
    public static function getERC20Balance_internal($url, $contract_hash, $address) {
        if (!Web3\Utils::isAddress($contract_hash)) {
            self::LOGD("传入的 contract_hash `{$contract_hash}' 不是一个合法的地址");
            return null;
        }
        
        self::LOGD("使用 %s 查询 ERC20(%s) 的账户（%s）余额", $url, $contract_hash, $address);
        
        
        $contract_hash = strtolower($contract_hash);
        $provider = new Web3\Providers\HttpProvider(new Web3\RequestManagers\HttpRequestManager($url, $timeout = 10));
        $contract = new Web3\Contract($provider, self::ERC20_ABI);
        
        
        
        $info = self::getERC20Info_internal($url, $contract_hash);
        
        if (!isset($info['decimals'])) {
            self::LOGD("无法获取到 `{$contract_hash}' 的小数点位数信息，无法获取余额，直接返回 false");
            return false;
        }
        
        $decimals = $info['decimals'];
        bcscale($decimals + 2);
        
        $balance = '';
        
        
        $functionData = $contract->at($contract_hash)->call('balanceOf', $address, function($a, $b) use (&$balance) {
            if ($b !== null) {
                $balance = (string)current($b);
            }
        });
        
        $balance_ret = bcdiv($balance, bcpow(10, $decimals));
        
        self::LOGD("查询到账户（%s）的 ERC20（%s）原始余额是 `%s'，小数点位数应为 %s，计算出来的最终余额是 %s", $address, $contract_hash, $balance, $decimals, $balance_ret);
        
        return $balance_ret;
    }
    
    
    
    /**
     * 判断一个 tx 是否成功完成
     * 
     * @return mixed        返回 null 表示暂时无法获知指定的交易是否成功，false 表示失败，true 表示成功
     */
    public function isTxSuccess($tx_id) {
        /*
        $tx = $this->getTx($tx_id);
        if (!$tx) {
            self::LOGD("找不到 hash 为 `{$tx_id}' 的以太坊交易记录，直接返回 false");
            return false;
        }
        */
        
        $receipt = $this->getTxReceipt($tx_id);
        if ($receipt === null) {
            self::LOGD("暂时找不到 hash 为 `{$tx_id}' 的以太坊交易记录 receipt，直接返回 null");
            return null;
        }
        else if (!$receipt) {
            self::LOGD("找不到 hash 为 `{$tx_id}' 的以太坊交易记录 receipt，直接返回 false");
            return false;
        }
        
        
        $is_success = false;
        
        /*
        foreach ($receipt['logs'] as $log) {
            // 只有在 log 中存在 transactionHash 为本 tx 的 hash，并且 type 为 mined 时才认为合约执行成功
            if ($log['transactionHash'] != $tx_id) {
                continue;
            }
            
            if ($log['type'] != 'mined') {
                continue;
            }
            
            $is_success = true;
            break;
        }
        */
        
        /** 
         * 在 2017 年十月拜占庭硬分叉之后，可以根据 status 的值来判断是否成功。
         * 硬分叉之前，status 的值是 null
         * 硬分叉之后，status 的值在成功时是 0x1，失败时是 0x0
         * 
         * 由于我们不需要处理 2017 年 10 月之前的记录（我们系统尚未上线），所以这里直接判断 status 是否为 0x1
         */
        if (isset($receipt['status']) && $receipt['status'] == '0x1') {
            $is_success = true;
        }
        
        
        return $is_success;
    }
    
    public function isSmartContractSuccess($tx_id) {
        return $this->isTxSuccess($tx_id);
    }
    
    
    
    /**
     * 传入一个 tx 数据，判断这个 tx 的目标地址是不是一个 ERC20 合约
     */
    public function isERC20Target($tx) {
        $input = substr($tx['input'], 2);
        $method_id = substr($input, 0, 8);

        if ($method_id == 'a9059cbb') {
            /// transfer(address,uint256)
            /// 这是一个转账交易
            $to_address = substr($input, 8, 64);
            $to_address = '0x' . substr($to_address, -40);
            
            $sender_address = $tx['from'];
            $from_address = $tx['from'];   
            
            return true;
        }
        else if ($method_id == '23b872dd') {
            /// transferFrom(address,address,uint256)
            /// 这是一个转账交易
            $from_address = substr($input, 8, 64);
            $from_address = substr($from_address, -40);
            
            $to_address = substr($input, 8 + 64, 64);
            $to_address = '0x' . substr($to_address, -40);
            
            $sender_address = $tx['from'];
            return true;
        }
        else {
            /// 认为这不是一个 ERC20 目标
            return false;
        }
    }
    
    /**
     * 对一个目标地址为 ERC20 合约的 tx，抽取其中的 ERC20 转账信息
     * 
     * @return  返回一个数组，格式为: [ to => 收款地址, value => 十六进制金额，与 tx 格式相同， decimals => 小数位数 ]。如果目标地址不是 ERC20 合约则返回 null 
     */
    public function formatERC20($tx, $trace_address = '') {
        $input = '';
        $from = '';
        
        if ($trace_address == '') {
            $input = $tx['input'];
            $from = $tx['from'];
        }
        else {
            $traceData = $this->getTraceWithTraceAddress($tx['hash'], $trace_address);
            $input = $traceData['action']['input'];
            $from = $tx['from'];
            
            /**
             * 交易 from 地址可能与 action.from 地址不同。因为智能合约所有者可以直接调用一个智能合约方法，然后在子交易中创建一些给其他人的付款。
             * 于是这段处理就不再需要了
             */
            /*
            if ($tx['from'] != $traceData['action']['from']) {
                self::LOGD("内部交易 from 地址与母交易的 from 地址不一致，拒绝处理此交易，请人工确认此交易的合法性。tx_hash = `{$tx['hash']}', trace_address = `{$trace_address}'");
                return false;
            }
            */
            
            if (!isset($traceData['result']['output'])) {
                self::LOGD("无法确定交易 `{$tx['tx_id']}' 的子交易 `{$trace_address}' 的状态，因为不存在 result.output 字段，直接认为交易失败");
                return false;
            }
            
            if ($this->hex2dec($traceData['result']['output']) != 1) {
                self::LOGD("交易 `{$tx['tx_id']}' 的子交易 `{$trace_address}' 的 result.output 结果不是 1，认为此交易失败了");
                return false;
            }
        }
        
        
        return $this->formatERC20Input($input, $from);
        
    }
    
    /**
     * 传入一个 ERC20 交易的 input 数据，解析出这个 ERC20 的交易信息
     * 
     * @param input     ERC20 交易的 input 数据，可以带 0x 前缀也可以不带
     * @param from      该交易的来源地址
     * @return array    [ from => 来源, to => 目标地址, value => 十六进制格式的金额 ]
     */
    public function formatERC20Input($input, $from) {
        if (substr($input, 0, 2) == '0x') {
            $input = substr($input, 2);
        }
        
        $method_id = substr($input, 0, 8);
        
        $from_address = '';
        $to_address = '';
        $value = '';
        
        if ($method_id == 'a9059cbb') {
            /// transfer(address,uint256)
            /// 这是一个转账交易
            $to_address = substr($input, 8, 64);
            $to_address = '0x' . substr($to_address, -40);
            
            $value = substr($input, 8 + 64, 64);
            $value = '0x' . ltrim($value, '0');
            
            $sender_address = $from;
            $from_address = $from;   
        }
        else if ($method_id == '23b872dd') {
            /// transferFrom(address,address,uint256)
            /// 这是一个转账交易
            $from_address = substr($input, 8, 64);
            $from_address = substr($from_address, -40);
            
            $to_address = substr($input, 8 + 64, 64);
            $to_address = '0x' . substr($to_address, -40);
            
            $value = substr($input, 8 + 64 + 64, 64);
            $value = '0x' . ltrim($value, '0');
            
            $sender_address = $from;
        }
        else {
            /// 认为这不是一个 ERC20 目标
            self::LOGD("传入的数据 `%s' 不像是一个 ERC20 转账的交易，无法解析出 ERC20 转账信息", $input);
            return false;
        }
        
        return [
            'from' => $from_address,
            'to' => $to_address,
            'value' => $value,
        ];
    }
    
    
    
    
    /**
     * 创建一个 eth 交易
     * 
     * @param data.private_key      转出账户的私钥，以十六进制字符串表示
     * @param data.to               收款地址
     * @param data.value            金额，十六进制格式
     * @param data.nonce            如果不传则自动判断 nonce
     * @param data.gas_price        （可选）gas 价格，十六进制格式。如果不传则使用自动预测的价格
     * @param data.gasPrice         （可选）gas 价格，十六进制格式。如果不传则使用自动预测的价格。注意，gasPrice 和 gas_price 两个参数只能传一个
     * @param data.gas              （可选）gas 限制，十六进制格式。如果不传则自动猜测一个可能的 limit
     * @param data.chain_id         （可选）EIP 155 网络编号，默认根据运行环境自动选择（mainnet）. 备注： mainnet: 1, ropsten: 3
     * @return string               以 0x 开头的十六进制格式
     */
    public function makeRawTransaction($data) {
        /// 1. 检查数据合法性并预处理数据
        if (!isset($data['private_key'])) {
            $this->setLastError('未传入私钥数据');
            return false;
        }
        $private_key = $data['private_key'];
        unset($data['private_key']);
        
        
        /**
        /// 创建智能合约的时候不需要 to 参数，所以这里不对 to 参数进行检查
        if (!isset($data['to'])) {
            $this->setLastError('未提供收款地址');
            return false;
        }
        */
        
        if (!isset($data['value'])) {
            $this->setLastError('未填写金额');
            return false;
        }
        else if (substr($data['value'], 0, 2) != '0x') {
            $this->setLastError('金额必须以 0x 开头');
            return false;
        }
        
        $private_key_address = null;
        if (!isset($data['nonce'])) {
            $private_key_address = $this->privateKey2Address($private_key);
            if (!$private_key_address) {
                $this->setError('无法将私钥转换成地址，无法确定 nonce');
                return false;
            }
            
            //$data['nonce'] = $this->getTransactionCount($private_key_address);
            $data['nonce'] = $this->hex2dec($this->getNextNonce($private_key_address));
        }
        
        if (!isset($data['gas'])) {
            //$data['gas_limit'] = '0x' . gmp_strval(gmp_init(self::GAS_LIMIT, 10), 16);
            $data_estimate_gas = $data;
            $data_estimate_gas['from'] = $private_key_address;
            $data['gas'] = $this->getEstimateGas($data_estimate_gas);
            
            
            if (!$data['gas']) {
                self::LOGD("由于获取 gas 失败，尝试将 from 参数设为我们的热钱包地址后再次尝试 estimateGas");
            
                $data_with_hot_wallet = $data;
                throw new \Exception("需要修改下面的 hot_wallet.adress ");
                $data_with_hot_wallet['from'] = 'hot_wallet.address';
                $data['gas'] = $this->getEstimateGas($data_with_hot_wallet);
            }
            
            if (!$data['gas']) {
                $this->setError('无法获取预计的 gas 数量，无法创建交易数据: ' . $this->getLastMessage());
                return false;
            }
            
            
            /// 由于预计出来的 gas 数量可能不正确，我们将 gas 数量乘以 2，保证 gas 充足
            $data['gas'] = $this->hexMul($data['gas'], '0x2');
        }
        

        if (isset($data['gas_price']) && isset($data['gasPrice'])) {
            $this->setLastError("不能同时指定 gas_price 和 gasPrice 参数");
            return false;
        }
        else if (isset($data['gas_price'])) {
            /// 什么都不用做
        }
        else if (isset($data['gasPrice'])) {
            $data['gas_price'] = $data['gasPrice'];
            unset($data['gasPrice']);
        }
        else {
            self::LOGD("由于没有传入 gas 信息，自动获取一个 gas 数据");
            $data['gas_price'] = $this->getGasPrice();
        }
        
        if (!$data['gas_price'] || $this->hex2dec($data['gas_price']) <= 0) {
            $this->setLastError("无法获取 gas_price，无法组装 ethereum 交易");
            return false;
        }
        
        
        if (!isset($data['chain_id'])) {
            /*
            if (C('debug')) {
                self::LOGD("由于没有传入 chain_id 参数，在当前测试环境下自动设为 3 (ropsten)");
                $data['chain_id'] = 3;
            }
            else {
                self::LOGD("由于没有传入 chain_id 参数，在当前生产环境下自动设为 1 (mainnet)");
                $data['chain_id'] = 1;
            }*/
            throw new \Exception("未传入 chain_id 参数，无法处理");
        }
        
        $input_data = '0x';
        if (isset($data['data'])) {
            $input_data = $data['data'];
            unset($data['data']);
        }
        
        
        
        /// 2. 要求进行签名
        $query_string = http_build_query($data);
        $url = "http://127.0.0.1:8081/?{$query_string}";
        
        // $param = [
        //     'method' => 'post',
        //     'body' => [
        //         'private_key' => $private_key,
        //         'data' => $input_data,
        //     ],
        // ];
        // $result = Ext_Http::request($url, $param);

        $result = (new \GuzzleHttp\Client())->
            request('post', $url, [
                'body' => [
                    'private_key' => $private_key,
                    'data' => $input_data,
                ],
            ])->
            getBody();

        if (!$result) {
            $this->setLastError("本地 eth 签名服务请求失败，原始返回数据: " . var_export($result, true));
            return false;
        }
        
        $j = json_decode($result, true);
        if (!$j) {
            $this->setLastError('无法将本地 eth 签名服务返回的数据解析为 JSON，原始的返回数据是: ' . var_export($result, true));
            return false;
        }
        
        if (!isset($j['rawData'])) {
            $this->setLastError('本地 eth 签名服务返回的数据没有携带 rawData 字段，原始返回的数据是: ' . var_export($result, true));
            return false;
        }
        
        
        /// 3. 返回原始的交易数据
        return '0x' . $j['rawData'];
    }
    
    
    
    /**
     * 发布一个原始的 eth 交易
     * 
     * @param raw       原始的交易数据，以 0x 开头的十六进制字符串
     */
    public function sendRawTransaction($raw, &$err = '') {
        $ret = $this->rpc('eth_sendRawTransaction', [$raw], $err);
        
        self::LOGD("发送交易返回的原始数据是 %s. 原始的请求数据是 %s", var_export($ret, true), var_export($raw, true));
        
        return $ret;
    }
    
    
    /**
     * 创建一个 eth ERC20 交易
     * 
     * @param data.private_key      转出账户的私钥，以十六进制字符串表示
     * @param data.contract_address 智能合约地址
     * @param data.to               收款地址
     * @param data.value            ERC20 金额，0x 开头的十六进制格式
     * @param data.eth_value        ETH 金额，0x 开头的十六进制格式，如果不传则默认为 0x0
     * @param data.nonce            
     * @param data.gas_price        （可选）gas 价格，十六进制格式。如果不传则使用自动预测的价格
     * @param data.gas              （可选）gas 限制，十六进制格式。如果不传则使用默认的限制 900000
     * @param data.chain_id         （可选）EIP 155 网络编号，默认根据运行环境自动选择 1（mainnet）. 备注： mainnet: 1, ropsten: 3
     * @return string               以 0x 开头的十六进制格式
     */
    public function makeERC20RawTransaction($data) {
        /// 0. 判断数据合法性
        if (!isset($data['contract_address'])) {
            $this->setLastError('必须传入 contract_address');
            return false;
        }
        
        
        /// 1. 预处理输入数据
        $to = $data['to'];
        $value = $data['value'];
        
        $data['to'] = $data['contract_address'];
        
        if (isset($data['eth_value'])) {
            $data['value'] = $data['eth_value'];
            unset($data['eth_value']);
        }
        else {
            $data['value'] = '0x0';
        }
        
        
        
        unset($data['contract_address']);
        
        
        
        /// 2. 构建 data 数据
        /**
         * 例子
        $input = [
            'a9059cbb',     // methodID: transfer(address,uint256) 
            '0000000000000000000000005c249541af4053191eb2b470618d66fe3d3b369b'  // 收款地址 
            '0000000000000000000000000000000000000000000000000000000000000006'  // 付款金额
        ];
        */
        
        if (substr($to, 0, 2) == '0x') {
            $to = substr($to, 2);
        }
        
        if (substr($value, 0, 2) != '0x') {
            $this->setError('ERC 付款金额必须以 0x 开头');
            return false;
        }
        else {
            $value = substr($value, 2);
        }
        
        $input = [ 
            'a9059cbb',
            '000000000000000000000000' . $to ,
            str_repeat('0', 64 - strlen($value)) . $value,
        ];
        $input = implode('', $input);
        
        $data['data'] = '0x' . $input;
        
        
        /// 3. 计算 gasLimit
        /**
         * 目前有一个问题，当交易是 ERC20 交易的时候，eth_estimateGas 返回的 gasLimit 总是不足，为此，我们需要人工增加这个值。
         * 现在做法如下：将 eth_estimateGas 返回的值 * 5，将此作为最终需要使用的 gas，这里的 5 是一个经验值
         */
         
        /// 在进行 ERC20 付款的时候，必须提供 from 参数才能正确执行合约，所以我们在这里直接把 from 地址设为我们的热钱包地址
        $data_estimate_gas = $data;
        if (!isset($data_estimate_gas['from'])) {
            throw new \Exception("需要添加 hot_wallet.address");
//            $data_estimate_gas['from'] = C('hot_wallet.address');
            self::LOGD("由于没有提供 from 参数，我们将热钱包地址({$data_estimate_gas['from']})作为 from 参数填入 ERC20 数据，以便计算 estimateGas");
        }
        $gasLimit = $this->getEstimateGas($data_estimate_gas);
        
        
        if (!$gasLimit) {
            self::LOGD("由于获取 gas 失败，尝试将 from 参数设为我们的热钱包地址后再次尝试 estimateGas");
            
            $data_with_hot_wallet = $data;
            throw new \Exception("需要添加热钱包地址");
//            $data_with_hot_wallet['from'] = C('hot_wallet.address');
            $gasLimit = $this->getEstimateGas($data_with_hot_wallet);
        }
        
        if (!$gasLimit) {
            $this->setError("无法获取预估的 gas 数量，无法继续创建交易原始数据");
            return false;
            //self::LOGD("由于无法获取 ERC20 交易的预计 gas 数量，使用一个 80000 作为数量");
            //$gasLimit = '0x13880';  /// = 十进制 80000
        }
        $gasLimitERC20 = $this->hexMul($gasLimit, '0x5');
        
        self::LOGD("为 ERC20 计算出来的 gasLimit 是 {$gasLimit} * 5 = {$gasLimitERC20}");
        
        $data['gas'] = $gasLimitERC20;
        
        
        
        return $this->makeRawTransaction($data);
    }
    
    
    /**
     * 返回十进制的数量
     */
    public function getTransactionCount($address) {
        $result = $this->rpc('eth_getTransactionCount', [$address, 'latest']);
        
        if ($result) {
            $result = substr($result, 2);
            $result = gmp_strval(gmp_init($result, 16), 10);
        }
        
        return $result;
    }
    
    
    public function privateKey2Address($private_key) {
        throw new \Exception("此方法尚未改造");
        // $params = [
        //     'method' => 'post',
        //     'body' => [
        //         'private_key' => $private_key,
        //     ]
        // ];
        // $result = Ext_Http::request('http://127.0.0.1:8082', $params);
        
        // if (!$result) {
        //     return false;
        // }
        
        // $j = json_decode($result, true);
        // if (!$j) {
        //     self::LOGD("无法将私钥转地址服务的返回解析为 JSON: %s", $result);
        //     return false;
        // }
        
        // return $j['address'];
    }
    
    
    /**
     * 使用指定的秘钥，发送一个以太币到指定的地址
     * 
     * @param private_key       转出账户的私钥
     * @param address           收款地址
     * @param amount            收款金额，以十进制小数表示，单位为 ETH
     * @param opt.gas_price     手动指定一个 gas_price，格式为 0x 开头的十六进制，单位为 wei
     * @return mixed            成功时返回 tx_id，失败返回 false
     */

//     public function sendETH($private_key, $address, string $amount, $data = '0x', $opt = []) {
//         bcscale(20);
        
//         $value = number_format($amount, 18);
//         $value = bcmul($value, bcpow(10, 18));
//         $value = rtrim($value, '0');
//         $value = rtrim($value, '.');
        
//         if ($value === '') {
//             $value = '0';
//         }
        
        
//         if (strpos('.', $value) !== FALSE) {
//             $this->setError('输入的 amount 超出 ETH 精度范围');
//             return false;
//         }
        
//         $value = gmp_strval(gmp_init($value, 10), 16);
//         $value = '0x' . $value;


//         $from = $this->privateKey2Address($private_key);
        
//         self::LOGD("准备从 `{$from}' 向 `{$address}' 发送 `{$amount}' 个 ETH，转换出来的 value 是 `{$value}'");

        
//         /*
//         $nonce = $this->getTransactionCount($from);
        
//         if (!$nonce) {
//             $this->setError('无法获取地址的交易数量，无法确定 nonce，无法发送交易');
//             return false;
//         }
//         else {
//             $nonce = substr($nonce, 2);
//             $nonce = gmp_strval(gmp_init($nonce, 16), 10);
//         }
//         */
//         $nonce = $this->getNextNonce($from);
//         if ($nonce === false) {
//             $this->setError("无法获取地址 `{$from}' 的 nextNonce，无法继续支付，返回 false");
//             return false;
//         }
//         else {
//             $nonce = $this->hex2dec($nonce);
//         }
        
        
//         //$count = importHelper('ethereum')->getTransactionCount($from);
        
//         $raw = [
//             'private_key' => $private_key,
//             'to' => $address,
//             'value' => $value,
//             'gas_price' => importHelper('ethereum')->getGasPrice(),
//             'data' => $data,
//             'nonce' => $nonce,
//         ];

//         if (isset($opt['gas_limit'])) {
//             self::LOGD("使用传入的 gas_limit ({$opt['gas_limit']}) 替代预估的 gas");
//             $raw['gas'] = '0x' . $this->dec2hex($opt['gas_limit']);
//         }
//         else {
//             $estimate_gas = importHelper('ethereum')->getEstimateGas($raw);
//             self::LOGD("当前交易预计需要使用的 gas 数量是 `{$estimate_gas}'");

//             $raw['gas'] = $estimate_gas;
//         }


//         if (isset($opt['gas_price'])) {
//             self::LOGD("使用传入的 gas_price({$opt['gas_price']}) 覆盖自动获取的 gas_price({$raw['gas_price']})");
//             $raw['gas_price'] = $opt['gas_price'];
//         }        
        
//         if ($this->hex2dec($raw['gas_price']) <= 0) {
//             $this->setError("指定的（或预测的）gasPrice 为 0，拒绝发送交易，避免交易无法被打包");
//             return false;
//         }
        

//         $raw = $this->makeRawTransaction($raw);
        
//         if (!$raw) {
//             $this->setError("无法将交易编码为 raw 数据");
//             return false;
//         }
        
        
//         $tx_id = $this->sendRawTransaction($raw);
//         if (!$tx_id) {
//             self::LOGD("发送 raw 交易数据失败");
//             return false;
//         }
//         else {
//             return $tx_id;
//         }
//     }
    
    /**
     * 使用指定的秘钥发送一个 ERC20 交易
     * 
     * @param private_key   指定的地址
     * @param address       ERC20 收款地址
     * @param coin_nam     本站保存的 ERC20 的货币简称
     * @param erc20_amount  要转入的 ERC20 的总额，十进制格式
     * @param eth_amount    （不再支持）要转入的 ETH 总额，如果不传则默认为 0
     * @return mixed        成功时返回 tx_hash, 失败返回 false
     */
    // public function sendERC20($private_key, $address, $coin_name, $erc20_amount) {
    //     bcscale(20);
        
    //     /// 1. 格式化 ETH 金额为 0x 开头的形式
    //     /*
    //     $eth_value = number_format($eth_amount, 18);
    //     $eth_value = bcmul($eth_value, bcpow(10, 18));
    //     $eth_value = rtrim($eth_value, '0');
    //     $eth_value = rtrim($eth_value, '.');
        
    //     if ($eth_value === '') {
    //         $eth_value = '0';
    //     }
        
        
    //     if (strpos('.', $eth_value) !== FALSE) {
    //         $this->setError('输入的 amount 超出 ETH 精度范围');
    //         return false;
    //     }
        
    //     $eth_value = gmp_strval(gmp_init($eth_value, 10), 16);
    //     $eth_value = '0x' . $eth_value;
    //     */

    //     /// 2. 格式化 ERC20 为 0x 开头的形式
    //     $coin = E('coin')->getByCoin($coin_name);
    //     $erc20_info = $this->getERC20Info($coin['erc20_contract_hash']);
    //     if (!$erc20_info) {
    //         $this->setError("无法获取到 ERC20 货币 `{$coin_name}'({$coin['erc20_contract_hash']})的信息，无法进行 ERC20 转账");
    //         return false;
    //     }
        
    //     $decimals = $erc20_info['decimals'];
    //     if ($decimals > 18) {
    //         $this->setError("由于 ERC20 货币 `{$coin_name}' 的小数位数({$decimals})超过了 18 位，不支持此 ERC20 的转账操作");
    //         return false;
    //     }
        
    //     $erc20_value = number_format($erc20_amount, $decimals, '.', '');
    //     $erc20_value = bcmul($erc20_value, bcpow(10, $decimals));
    //     $erc20_value = rtrim($erc20_value, '0');
    //     $erc20_value = rtrim($erc20_value, '.');
        
    //     if ($erc20_value === '') {
    //         $erc20_value = '0';
    //     }
        
    //     if (strpos('.', $erc20_value) !== FALSE) {
    //         $this->setError("输入的 erc20_value 超出了 ERC20 货币 `{$coin_name}' 的小数精度范围");
    //         return false;
    //     }
        
    //     $erc20_value = gmp_strval(gmp_init($erc20_value, 10), 16);
    //     $erc20_value = '0x' . $erc20_value;
        
        
    //     /// 3. 准备转账数据
    //     $from = $this->privateKey2Address($private_key);
        
    //     self::LOGD("准备从 `{$from}' 向 `{$address}' 发送 `{$erc20_amount}' 个 `{$coin_name}'，转换出来的 value 是 `{$erc20_value}'");
        
    //     $data = [
    //         'contract_address' => $coin['erc20_contract_hash'],
    //         'private_key' => $private_key,
    //         'to' => $address,
    //         'value' => $erc20_value,
    //         //'eth_value' => $eth_value,
    //         'eth_value' => '0x0',
    //         'nonce' => $this->getNextNonce($from),
    //     ];
    //     $transaction = $this->makeERC20RawTransaction($data);
        
    //     if (!$transaction) {
    //         $this->setError("无法创建 RAW ERC20 交易数据，无法继续发送 ERC20 货币");
    //         return false;
    //     }
        
        
    //     /// 4. 发布交易
    //     $tx_hash = $this->sendRawTransaction($transaction);
    //     if (!$tx_hash) {
    //         $this->setError("无法发布 RAW ERC20 交易，无法继续发送 ERC20 货币");
    //     }
        
    //     return $tx_hash;
    // }
    
    
    /**
     * 获取一个或多个地址的交易记录列表
     * 
     * @param address        可以传入一个地址，也可以传入地址数组
     */
    // public function getTransactionList($address) {
    //     bcscale(20);
        
    //     $result = $this->getTransactionsByAddress($address);
    //     if (!is_array($result)) {
    //         return $result;
    //     }
        
    //     $addresses = null;
    //     if (is_array($address)) {
    //         $addresses = $address;
    //     }
    //     else {
    //         $addresses = [$address];
    //     }


    //     $addresses = array_map(function ($v) {
    //         return strtolower($v);
    //     }, $addresses);
    
        
    //     $last_block_number = $this->getLastBlockNumber();
    
        
    //     $txs = [];
    //     foreach ($result as $v) {
    //         $hash = $v['transactionHash'];
            
    //         $address = '';
            
    //         $amount = null;
    //         $amount = $this->hex2dec($v['action']['value']);
    //         $amount = $this->wei2ether($amount);
                
    //         if (in_array($v['action']['from'], $addresses)) {
    //             $address = $v['action']['from'];
    //             $amount = bcmul($amount, -1);
    //         }
    //         else {
    //             $address = $v['action']['to'];
    //         }
            
    //         //$amount = Ext_Misc::trimr0($amount);
    //         $amount = $amount;
            
            
    //         $block = $this->getBlock($v['blockNumber']);
    //         $create_time = $this->hex2dec($block['timestamp']);
            
    //         $tx = $this->getTx($hash);
            
            
    //         $gasUsed = $v['result']['gasUsed'] ?? '0x0';        /// 当交易执行失败的时候，不存在 result 字段
    //         $gasUsed = $this->hex2dec($gasUsed);
    //         $gasUsed += 21000;                      /// parity 返回的 gasUsed 字段指的是，在 21000 个基础 gas 之外，还消耗了多少 gas（任何交易至少都会需要 21000 的 gas）
            
    //         $gasPrice = $tx['gasPrice'];
    //         $gasPrice = $this->hex2dec($gasPrice);
            
    //         $fee = bcmul($gasUsed, $gasPrice);
    //         $fee = $this->wei2ether($fee);
            
    //         $txs[$hash] = [
    //             'address' => $address,
    //             'amount' => $amount,
    //             'tx_hash' => $hash,
    //             'confirm_count' => $last_block_number - $v['blockNumber'],
    //             'create_time' => $create_time,
    //             'from' => $v['action']['from'],
    //             'to' => $v['action']['to'] ?? '',           /// 当交易失败的时候，to 可能是空的
    //             'fee' => $fee,
    //             //'v' =>$v,
    //         ];
    //     }
        
        
    //     /// 排序结果
    //     $create_times = array_column($txs, 'create_time');
    //     array_multisort($create_times, SORT_DESC, $txs);
        
        
    //     /// 返回结果
    //     $txs = array_values($txs);
        
    //     return $txs;
        
    // }
    
    
    // public function getTransactionsByAddress($address) {
    //     $addresses = null;
        
    //     if (!is_array($address)) {
    //         $addresses = [$address];
    //     }
    //     else {
    //         $addresses = $address;
    //     }
        
        
    //     $to_block = $this->getLastBlock();
    //     $to_block = $to_block['number'];
        
    //     /// 1. 尝试从缓存中获取 from_block 的值，以便减少搜索范围
    //     $address_first_blocks = [];
    //     foreach ($addresses as $a) {
    //         $first_block = $this->getAddressFirstBlock($a);
    //         if ($first_block) {
    //             $address_first_blocks[$a] = $first_block;
    //         }
    //         else {
    //             $address_first_blocks[$a] = 1;
    //         }
    //     }
        
    //     $first_block = min($address_first_blocks);
    //     $first_block = '0x' . $this->dec2hex($first_block);
        
    //     /// 2. 进行查询
    //     $err1 = null;
    //     $err2 = null;
        
    //     $result1 = $this->rpc('trace_filter', [['fromAddress' => $addresses, 'fromBlock' => $first_block, 'toBlock' => $to_block]], $err1);
    //     $result2 = $this->rpc('trace_filter', [['toAddress' => $addresses, 'fromBlock' => $first_block, 'toBlock' => $to_block]], $err2);
        
        
    //     if (!is_array($result1) && !$result1) {
    //         $this->setMessage(sprintf(__("无法根据地址搜索记录: %s"), $err1));
    //         return false;
    //     }

    //     if (!is_array($result2) && !$result2) {
    //         $this->setMessage(sprintf(__("无法根据地址搜索记录: %s"), $err2));
    //         return false;
    //     }
        
    //     $result = array_merge($result1, $result2);
        
        
    //     /// 3. 更新 first_block 持久缓存
    //     foreach ($address_first_blocks as $a => $b) {
    //         if ($b > 1) {
    //             /// 这个地址已经有首块缓存记录了，跳过
    //             continue;
    //         }
            
    //         $a = strtolower($a);
    //         $b = PHP_INT_MAX;
    //         foreach ($result as $k => $v) {
    //             if (strtolower($v['action']['from']) != $a && strtolower($v['action']['to']) != $a) {
    //                 continue;
    //             }
                
    //             if ($v['blockNumber'] < $b) {
    //                 $b = $v['blockNumber'];
    //             }
    //         }
            
    //         if ($b != PHP_INT_MAX) {
    //             $this->setAddressFirstBlock($a, $b);
    //         }
    //         else {
    //             //self::LOGD("由于本次查询结果中没有查找到 `{$a}' 地址的交易记录，不会更新此地址的首次出现区块号缓存");
    //             self::LOGD("虽然本次查询结果中没有查找到 `{$a}' 地址的交易记录，但我们仍然可以更新此地址的首次出现区块号缓存为当前最高块");
    //             $this->setAddressFirstBlock($a, $this->hex2dec($to_block));
    //         }
    //     }
        
        
    //     return $result;
    // }
    
    
    
    // public function getAddressFirstBlock($address) {
    //     $address = strtolower($address);
    //     $key = "eth_address_first_block_{$address}";
        
    //     $result = E('kv')->get($key);
        
    //     if ($result) {
    //         self::LOGD("ETH 地址 `{$address}' 首次出现在 `{$result}' 号区块");
    //     }
    //     else {
    //         self::LOGD("目前没有 ETH `{$address}' 首次出现的区块记录");
    //     }
        
    //     return $result;
    // }
    
    // public function setAddressFirstBlock($address, $num) {
    //     $address = strtolower($address);
    //     $key = "eth_address_first_block_{$address}";
        
    //     E('kv')->set($key, $num);
        
    //     self::LOGD("将 ETH 地址 `{$address}' 首次出现的区块数缓存设置为了 `{$num}'");
        
    //     return true;
    // }
    
    
    /**
     * 将一段数据编码为 0x 开头的十六进制字符串，可直接传入 eth
     */
    public function encodeData($data) { 
        $ret = '0x' . bin2hex($data);
        return $ret;
    }

    
    
    /**
     * 获取一个预计的手续费 
     * 
     * @param ...   所需参数与 getEstimateGas() 方法相同
     * @return 预计的手续费，单位：ETH
     */
    public function getEstimateFee($data) {
        $gas = $this->getEstimateGas($data);
        if ($gas === false) {
            self::LOGD("无法获取预计的 gas 数量，无法计算预计 ETH 手续费金额");
            return null;
        }
        
        $gas_price = $this->getGasPrice();
        if ($gas_price === false) {
            self::LOGD("目前无法获取 gas_price，无法计算预计 ETH 手续费金额");
            return null;
        }
        
        $fee = $this->hexMul($gas, $gas_price);
        $fee = $this->hex2dec($fee);
        $fee = $this->wei2ether($fee);
        
        self::LOGD("当前的 gas 是 `%s'(%s), gas_price 是 `%s'(%s Gwei)，计算出来的 fee 是 `%s'", $gas, $this->hex2dec($gas), $gas_price, $this->hex2dec($gas_price) / 1000000000, $fee);
        
        return $fee;
    }
    
    /**
     * 获取一个预估的 ETH 转账手续费用
     */
    // public function getEstimateETHTransferFee() {
    //     $data = [
    //         'from' => C('hot_wallet.address'),
    //         'to' => C('hot_wallet.address'),
    //         'value' => '0x1',
    //     ];
        
    //     return $this->getEstimateFee($data);
    // }
    
    
    /**
     * 给定一个 ERC20 合约，计算此 ERC20 转账预计需要多少手续费
     */
    // public function getERC20EstimateFee($contract_hash) {
    //     $input = [
    //         'a9059cbb',
    //         '000000000000000000000000' . substr(C('hot_wallet.address'), 2),
    //         str_repeat('0', 63) . '1',
    //     ];
        
    //     $data = [   
    //         'from' => C('hot_wallet.address'),
    //         'to' => $contract_hash,
    //         'data' => '0x' . implode('', $input),
    //     ];
        
    //     $fee = $this->getEstimateFee($data);
        
    //     return $fee;
    // }
    
    
    /**
     * 获取一个指定的 tx 的交易金额。单位：十进制 Ether
     */
    public function getTxAmount($tx_hash, $trace_address = '') {
        $t = $this->getTraceWithTraceAddress($tx_hash, $trace_address);
        if (!$t) {
            return null;
        }
        
        $amount = $t['action']['value'];
        $amount = $this->hex2dec($amount);
        $amount = $this->wei2ether($amount);
        
        return $amount;
    }
    
    
    /**
     * 获取一个指定的 ERC20 tx 的交易金额。以十进制格式返回以此 ERC20 货币计价的金额
     */
    public function getTxAmountERC20($tx_hash, $trace_address = '') {
        $t = $this->getTraceWithTraceAddress($tx_hash, $trace_address);
        
        if (!$t) {
            self::LOGD("无法获取 `{$tx_hash}'(trace_address=`{$trace_address}') 的 trace，无法继续获取交易金额");
            return null;
        }
        
        $erc20 = $this->getERC20Info($t['action']['to']);
        
        $decimals = (int)$erc20['decimals'];
        if ($decimals <= 0) {
            self::LOGD("取到的 ERC20 decimals 是 0(contract_hash = {$t['action']['to']})");
            return null;
        }
        
        
        bcscale($decimals + 1);
        
        $method = substr($t['action']['input'], 2, 8);
        $address = substr($t['action']['input'], 2 + 8 + (64 - 40), 40);
        $value = substr($t['action']['input'], 2 + 8 + 64, 64);
        
        $value = $this->hex2dec($value);
        $value = bcdiv($value, bcpow(10, $decimals));
        
        return $value;
    }
    
    
    
    /**
     * 给定一个地址，返回此地址的尚未确认的交易
     * 
     * @param address       给定的地址
     * @param contract_hash 如果要查询 ERC20 交易，则使用此参数指定合约地址，查询 ETH 不需要指定此参数或留空
     * @param n         多少个区块以内的数据被认为是未确认的
     * @return          返回内容结构如下所示：
                            'address' => $address['address'],
                            'tx_id' => $v['tx_id'],
                            'confirm_count' => (int)$confirm_count,
                            'amount' => 金额，正数为收入，负数为支出
                            'tx_time' => (int)$ethereumHelper->hex2dec($block['timestamp']),
                            'trace_address' => 格式化后的 traceAddress
                            'tx_n => 对应 transactionPosition 字段
                            'block_number' => 所在的块的高度

     */
    // public function getUnconfirmedTransactionsByAddress($address, $contract_hash = '', $n = 12) {
    //     $address = strtolower($address);
        
    //     $height = $this->getLastBlockNumber();
        
    //     $txs = [];
    //     for ($i = $height; $i >= $height - $n; $i--) {
    //         $result = $this->getFormattedBlockTransactions($i, $contract_hash);
            
    //         foreach ($result as $k => $v) {
    //             if ($v['address'] == $address) {
    //                 $txs[] = $v;
    //             }
    //         }
    //     }
        
        
    //     return $txs;
    // }
    
    
    /**
     * 给出一个区块，返回这个区块的交易列表
     * 返回的交易列表格式与 getUnconfirmedTransactionsByAddress 中的格式相同
     * 
     * @param block_numbers     指定的区块
     * @param contract_hash     如果要查询 ERC20 交易，则使用此参数指定 ERC20 的合约地址，查询 ETH 则不需要传此参数
     */
    // public function getFormattedBlockTransactions($block_number, $contract_hash = '') {
    //     bcscale(20);
        
    //     $contract_hash = strtolower($contract_hash);
        
    //     /// 1. 尝试从缓存中查询
    //     $redis = Ext_Cache_Redis::getInstance()->getRedis();
    //     $key = "cache_eth_formatted_block_txs_{$block_number}_{$contract_hash}";
    //     $key = "test";
        
    //     $txs = $redis->get($key);
        
    //     if (is_array($txs)) {
    //         $redis->expire($key, 86400);
    //         return $txs;
    //     }
            
        
    //     /// 2. 从客户端中查询数据
    //     $block_number_hex = '0x' . dechex($block_number);
    //     $block_height = $this->getLastBlockNumber();
    //     $block = $this->getBlock($block_number, false);
        
    //     $traces = $this->rpc('trace_block', [$block_number_hex]);
    //     if (!$traces) {
    //         self::LOGD("无法获取 ETH 第 `{$block_number}' 个区块的 trace，无法继续交易列表");
    //         return [];
    //     }
        
    //     $erc20_info = $this->getERC20Info($contract_hash);
    //     if (!$erc20_info) {
    //         self::LOGD("无法获取到 `{$contract_hash}' 的 ERC20 信息，无法继续查询交易");
    //         return [];
    //     }
        
        
    //     $txs = [];
    //     $tx_time = $this->hex2dec($block['timestamp']);
    //     $confirm_count = $block_height - $block_number;
        
    //     foreach ($traces as $t) {
    //         if (isset($t['type']) && $t['type'] == 'reward') {
    //             /// 这是一个挖矿产出交易
    //             continue;
    //         }
            
    //         if (!isset($t['result']['output'])) {
    //             self::LOGD("由于 `%s'(%s) 没有 result.output 字段，认为这个交易失败了，跳过", $t['transactionHash'], json_encode($t['traceAddress']));
    //             continue;
    //         }
    //         if (isset($t['error'])) {
    //             self::LOGD("由于 `%s'(%s) 存在 error 字段（内容是`%s'），认为这个交易失败了，跳过", $t['transactionHash'], json_encode($t['traceAddress']), $t['error']);
    //             continue;
    //         }
            
    //         if ($t['action']['callType'] != 'call') {
    //             self::LOGD("由于 `%s'(%s) 的 callType 不是 call，不需要处理这种类型的交易，跳过", $t['transactionHash'], json_encode($t['traceAddress']));
    //             continue;
    //         }
            
    //         $amount = $this->wei2ether($this->hex2dec($t['action']['value']));
    //         $from = $t['action']['from'];
    //         $to = $t['action']['to'];
            
    //         if ($erc20_info) {
    //             if (strlen($t['action']['input']) <= 2) {
    //                 continue;
    //             }
                
    //             $erc20_tx = $this->formatERC20Input($t['action']['input'], $from);
    //             if (!$erc20_tx) {
    //                 self::LOGD("由于 `%s'(%s) 交易中无法解析出 ERC20 交易数据，不会处理这个交易，跳过", $t['transactionHash'], json_encode($t['traceAddress']));
    //                 continue;
    //             }
                
    //             if (strtolower($t['action']['to']) != $contract_hash) {
    //                 continue;
    //             }
                
                
    //             $to = $erc20_tx['to'];
                
    //             $amount = $this->hex2dec($erc20_tx['value']);
    //             $amount = bcdiv($amount, bcpow(10, $erc20_info['decimals']));
    //             //$amount =  Ext_Misc::trimr0($amount);
    //         }
            
            
    //         $txs[] = [
    //             'address' => $from,
    //             'tx_id' => $t['transactionHash'],
    //             'trace_address' => implode('/', $t['traceAddress']),
    //             'confirm_count' => $confirm_count,
    //             'block_number' => $block_number,
    //             'tx_n' => $t['transactionPosition'],
    //             //'amount' => Ext_Misc::trimr0(bcmul($amount, '-1')),
    //             'amount' => bcmul($amount, '-1'),
    //             'decimals' => $erc20_info['decimals'],
    //             'value' => $this->hex2dec($erc20_tx['value']),
    //             'value2' => $erc20_tx['value'],
    //             'tx_time' => $tx_time,
    //             'direction' => 'outcome',
    //             'contract_hash' => $contract_hash,
    //         ];
            
    //         $txs[] = [
    //             'address' => $to,
    //             'tx_id' => $t['transactionHash'],
    //             'trace_address' => implode('/', $t['traceAddress']),
    //             'confirm_count' => $confirm_count,
    //             'block_number' => $block_number,
    //             'tx_n' => $t['transactionPosition'],
    //             'amount' => Ext_Misc::trimr0($amount),
    //             'decimals' => $erc20_info['decimals'],
    //             'value' => $this->hex2dec($erc20_tx['value']),
    //             'value2' => $erc20_tx['value'],
    //             'tx_time' => $tx_time,
    //             'direction' => 'income',
    //             'contract_hash' => $contract_hash,
    //         ];
    //     }
        
        
    //     /// 3. 将结果写入缓存
    //     $redis->set($key, $txs, 86400);
        
    //     return $txs;
    // }
    
    
    protected function setLastError($error) {
        $this->last_error[] = $error;
        
        if ($this->error_as_message) {
            self::LOGD($error);
        }
        else {
            self::LOGD($error);
        }
        
        return true;
    }
    
    protected function setError($error) {
        return $this->setLastError($error);
    }
    
    public function setErrorAsMessage($bool) {
        $this->error_as_message = $bool;
    }
    
    public function getLastMessage() {
        return end($this->last_error);
    }
    
    public function setMessage($msg) {
        self::LOGD("设置最后消息: $msg");
        $this->last_error[] = $msg;
    }
    
}

