<?php

namespace common\models;

class Utility
{

    /**
     * 获取客户端ip.
     *
     * @return string.
     */
    public static function getIpAddress()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && !empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ipAddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $ipAddress = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ipAddress = getenv("HTTP_CLIENT_IP");
        } else {
            $ipAddress = "unknown";
        }
        return $ipAddress;
    }

    /**
     * 获取服务端ip.
     *
     * @return string.
     */
    public static function getServerIpAddress()
    {
        $ipAddress = '';
        if (isset($_SERVER)) {
            if (isset($_SERVER['SERVER_ADDR'])) {
                $ipAddress = $_SERVER['SERVER_ADDR'];
            } elseif(isset($_SERVER['LOCAL_ADDR'])) {
                $ipAddress = $_SERVER['LOCAL_ADDR'];
            }
        }
        if(empty($ipAddress)) {
            $ipAddress = getenv('SERVER_ADDR');
        }
        if(empty($ipAddress)) {
            exec('/sbin/ifconfig eth0 | sed -n \'s/^ *.*addr:\\([0-9.]\\{7,\\}\\) .*$/\\1/p\'',$arr);
            $ipAddress = $arr[0];
        }

        return $ipAddress;
    }

    /**
     * 多维数组转为字符串.
     *
     * @param string $source 源数据.
     * @param string $flag 间隔标记.
     *
     * @return string.
     */
    public static function arrayToString($source, $flag = '|')
    {
        $result = '';
        if (is_array($source) && !empty($source)) {
            foreach ($source as $value) {
                if (is_string($value)) {
                    $result .= $value . $flag;
                } elseif (is_array($value)) {
                    $result .= self::arrayToString($value, $flag);
                }
            }
        } elseif (is_string($source)) {
            $result = $source;
        }
        if (substr($result, -1) == '|') {
            $result = substr($result,0,strlen($result)-1);
        }
        return $result;
    }

    /**
     * 验证参数值.
     *
     * @param array $param 验证数组.
     * @param array $check 需要验证的key.
     *
     * @return bool.
     */
    public static function checkParam(array $param, array $check, $checkEmpty = true)
    {
        if (!empty($check)) {
            foreach ($check as $value) {
                if (!isset($param[$value])) {
                    return false;
                } else {
                    if ($checkEmpty) {
                        if (empty($param[$value])) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * 判断是否为同一天.
     *
     * @param string $last_date 旧日期.
     * @param string $this_date 当前日期.
     *
     * @return bool.
     */
    public static function isDiffDays($last_date, $this_date)
    {

        if (($last_date['year'] === $this_date['year']) && ($this_date['yday'] === $last_date['yday'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 处理录入数据以回车/逗号/tab为分隔返回数组.
     *
     * @param string $input_data 输入的数据.
     *
     * @return array.
     */
    public static function getInputData($input_data)
    {
        $input_data = explode("\n", $input_data);
        $result_arr = array();

        foreach ($input_data as $row) {
            $line_arr = array();
            $row = trim($row);
            if (!empty($row)) {
                if (strstr($row, ",")) {
                    $line_arr = explode(",", $row);
                } else if (strstr($row, "\t")) {
                    $line_arr = explode("\t", $row);
                } else {
                    $line_arr[] = $row;
                }
            }
            $result_arr = array_merge($result_arr, $line_arr);
        }
        //去空
        $result_arr = array_filter($result_arr,
            function ($item) {
                return !empty(trim($item));
            }
        );
        $result_arr = array_unique($result_arr);   //去重
        $result_arr = array_values($result_arr);   //重组
        return $result_arr;
    }

    /**
     * @param $input_data
     * @return array
     */
    public static function getTextAreaData($input_data)
    {
        $input_data = explode("\n", $input_data);
        $result_arr = array();

        foreach ($input_data as $key => $row) {
            $line_arr = array();
            $row = trim($row);
            if (!empty($row)) {
                if (strstr($row, ",")) {
                    $result_arr[$key] = explode(",", $row);
                } else if (strstr($row, "\t")) {
                    $result_arr[$key] = explode("\t", $row);
                } else {
                    $result_arr[$key] = $row;
                }
            }
        }

        return $result_arr;
    }

    /**
     * 递归查找指定元素在多维数组的键.
     *
     * @param string $needle 待查找元素.
     * @param array $haystack 数组.
     *
     * @return bool|int|string
     */
    public static function recursive_array_search($needle, array $haystack)
    {
        foreach ($haystack as $key => $value) {
            $current_key = $key;
            if ($needle === $value || (is_array($value) && Utility::recursive_array_search($needle, $value))) {
                return $current_key;
            }
        }
        return false;
    }

    /**
     * 获取远程数据.
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public static function curlGetContents($url = '', $method = "GET", $data = array())
    {
        $query = array();
        $user_agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
        $curl = curl_init();

        if (!empty($data)) foreach ($data as $k => $v) {
            $query[] = $k . '=' . $v;
        }
        if (strtoupper($method) == 'GET' && $data) {
            $url .= '?' . implode('&', $query);
        } elseif (strtoupper($method) == 'POST' && $data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, implode('&', $query));
        }
        curl_setopt($curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
        $output = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new \Exception('Curl error: ' . curl_error($curl));
        }
        curl_close($curl);
        return json_decode($output, true);
    }

    public static function getDateTime($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        if (empty($time)) {
            return '0000-00-00 00:00:00';
        }
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * 时间是否为空.
     *
     * @param mixed $time
     * @return bool
     */
    public static function timeIsNull($time = null)
    {
        if(empty($time) || $time == '0000-00-00 00:00:00') {
            return true;
        }
        return false;
    }
    /**
     * 模型错误信息转换字符串.
     *
     * @param $errList
     * @param $line
     * @return string
     */
    public static function buildErrToStr($errList, $line = "\r\n")
    {
        if (!is_array($errList)) {
            return '';
        }

        $str = '';
        foreach ($errList as $item) {
            if (!is_array($item)) {
                return '';
            }
            foreach ($item as $err) {
                if (!is_string($err)) {
                    return '';
                }
                $str .= $err . $line;
            }
        }

        return $str;
    }

    /**
     * 导出csv.
     * @param $params
     * @return bool
     */
    public static function export_csv($params)
    {

        if (empty($params['list'])) {
            return false;
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $params['fileName'] . '.csv"');
        header('Cache-Control: max-age=0');

        // 打开PHP文件句柄，php://output 表示直接输出到浏览器
        $fp = fopen('php://output', 'a');

        $head = array();
        // 输出Excel列名信息
        foreach ($params['title'] as $i => $v) {
            // CSV的Excel支持GBK编码，一定要转换，否则乱码
            $title = is_array($v) ? $v['title'] : $v;
            $head[] = iconv('utf-8', 'gbk', $title);
        }

        // 将数据通过fputcsv写到文件句柄
        fputcsv($fp, $head);

        // 计数器
        $cnt = 0;
        // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 10000;

        foreach ($params['list'] as $idx => $item) {

            $cnt++;
            // 刷新一下输出buffer，防止由于数据过多造成问题
            if ($limit == $cnt) {
                ob_flush();
                flush();
                $cnt = 0;
            }
            $row = array();
            foreach ($params['title'] as $key => $val) {
                if (is_array($val) && $val['type'] == 'n') {
                    $value = $item[$val['field']];
                } else {
                    $value = $key == 'idx' ? $idx + 1 : '' . $item[$key];
                }
                $row[] = @iconv("utf-8", "gbk//IGNORE", $value);
            }

            fputcsv($fp, $row);
        }

        $totalLine = array();

        // 总计
        if (!empty($params['total'])) {
            foreach ($params['total'] as $v) {
                $totalLine[] = iconv('utf-8', 'gbk', $v);
            }
            fputcsv($fp, $totalLine);
        }

        return true;
    }

    /**
     * 数据导出Excel.
     *
     * @param $data
     * @param $header
     * @param string $title
     * @param string $filename
     * @return bool
     *
     * @example
     *  $data = [1, "小明", "25"];
     *  $header = ["id", "姓名", "年龄"];
     *  Myhelpers::exportData($data, $header);
     */
    public static function exportData($data, $header, $title = "simple", $filename = "data")
    {
        //require relation class files
        require_once("phpexcel/PHPExcel.php");
        require_once("phpexcel/PHPExcel/Writer/Excel2007.php");
        if (!is_array($data) || !is_array($header)) return false;
        $objPHPExcel = new \PHPExcel();
        // Set properties
//        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
//        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
//        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
//        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
//        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        //添加头部
        $hk = 0;
        foreach ($header as $k => $v) {
            $colum = \PHPExcel_Cell::stringFromColumnIndex($hk);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . "1", $v);
            $hk += 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($data as $key => $rows)  //行写入
        {
            $span = 0;
            foreach ($rows as $keyName => $value) // 列写入
            {
                $j = \PHPExcel_Cell::stringFromColumnIndex($span);
                $objActSheet->setCellValue($j . $column, $value);
                $span++;
            }
            $column++;
        }
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle($title);

        // Save Excel 2007 file
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        header("Pragma:public");
        header("Content-Type:application/x-msexecl;name=\"{$filename}.xlsx\"");
        header("Content-Disposition:inline;filename=\"{$filename}.xlsx\"");
        $objWriter->save("php://output");
    }


    /**
     * 导出Excel到本地
     * @param $data
     * @param $header
     * @param string $title
     * @param string $filename
     * @return bool
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public static function exportDataDownload($data, $header, $title = "simple", $filename = "data")
    {
        require_once("phpexcel/PHPExcel.php");
        require_once("phpexcel/PHPExcel/Writer/Excel2007.php");
        if (!is_array($data) || !is_array($header)) return false;
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        //添加头部
        $hk = 0;
        foreach ($header as $k => $v) {
            $colum = \PHPExcel_Cell::stringFromColumnIndex($hk);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . "1", $v);
            $hk += 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($data as $key => $rows)  //行写入
        {
            $span = 0;
            foreach ($rows as $keyName => $value) // 列写入
            {
                $j = \PHPExcel_Cell::stringFromColumnIndex($span);
                $objActSheet->setCellValue($j . $column, $value);
                $span++;
            }
            $column++;
        }
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle($title);

        //$user_path=$_SERVER['DOCUMENT_ROOT']."/blog/";
        $user_path = \Yii::$app->basePath . '/upload/';
        $filename = date('YmdHis',time()).'.xlsx';
        $filename = iconv("utf-8","gb2312",$filename);
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($user_path.$filename);
    }

    /**
     * 转换编码，将Unicode编码转换成可以浏览的utf-8编码.
     *
     * @param string $value 转码字符.
     *
     * @return mixed
     */
    public static function unicodeDecode($value)
    {
        $pattern = '/\\\u([\w]{4})/i';
        preg_match_all($pattern, $value, $matches);
        if (!empty($matches)) {
            for ($j = 0; $j < count($matches[0]); $j++) {
                $from = $matches[0][$j];
                if (strpos($from, '\\u') === 0) {
                    $code = base_convert(substr($from, 2, 2), 16, 10);
                    $code2 = base_convert(substr($from, 4), 16, 10);
                    $c = chr($code) . chr($code2);
                    $c = mb_convert_encoding($c, 'UTF-8', 'UCS-2');
                    $to = $c;
                    $value = str_replace($from, $to, $value);
                }
            }
        }
        return $value;
    }

    /**
     * 将数组转换为json串，并转换编码
     *
     * @param $array
     *
     * @return string
     */
    public static function arrayToJsonFormat($array = [])
    {
        return json_encode($array, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 浏览器参数拆分
     * 将数组转换为字符串，忽略key，所有的value以[]包裹。
     *
     * @param array $params
     * @return string
     */
    public static function arrayToStringFormat($params = [])
    {
        $return = [];
        if (!empty($params)) {
            if (is_array($params)) {
                foreach ($params as $value) {
                    if (!empty($value)) {
                        $return[] = self::arrayToStringFormat($value);
                    }
                }
            } else {
                $return[] = '[' . $params . ']';
            }
        }
        return implode(' ', $return);
    }

    public static function convertToString($data)
    {
        ob_start();
        print_r($data);
        $str = ob_get_contents();
        ob_end_clean();
        return $str;
    }

    public static function getRedStar($left = '1px', $right = '1px')
    {
        return '<span style="color: red;margin-left: ' . $left . ';margin-right: ' . $right . '" >*</span>';
    }

    public static function getAmount($num)
    {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        $num = round($num, 2);
        $num = $num * 100;
        if (strlen($num) > 10) {
            return "";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                $n = substr($num, strlen($num) - 1, 1);
            } else {
                $n = $num % 10;
            }
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            $num = $num / 10;
            $num = (int)$num;
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            $m = substr($c, $j, 6);
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j - 3;
                $slen = $slen - 3;
            }
            $j = $j + 3;
        }

        if (substr($c, strlen($c) - 3, 3) == '零') {
            $c = substr($c, 0, strlen($c) - 3);
        }
        if (empty($c)) {
            return "零元整";
        } else {
            return $c . "整";
        }
    }

    public static function multi_array_sort($multi_array, $sort_key, $sort = SORT_DESC)
    {
        if (is_array($multi_array)) {
            foreach ($multi_array as $row_array) {
                if (is_array($row_array)) {
                    $key_array[] = $row_array[$sort_key];
                } else {
                    return FALSE;
                }
            }
        } else {
            return FALSE;
        }
        array_multisort($key_array, $sort, $multi_array);
        return $multi_array;
    }

    public static function rehtml($data)
    {
        $data = preg_replace("/<em([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/em>/i", "", $data);
        $data = preg_replace("/<img([^>]+)?>/i", "", $data);
        $data = preg_replace("/<div([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/div>/i", "", $data);
        $data = preg_replace("/<table([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/table>/i", "", $data);
        $data = preg_replace("/<tr([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/tr>/i", "", $data);
        $data = preg_replace("/<td([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/td>/i", "", $data);
        $data = preg_replace("/<tbody([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/tbody>/i", "", $data);
        $data = preg_replace("/<strong([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/strong>/i", "", $data);
        $data = preg_replace("/<p([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/p>/i", "", $data);
        $data = preg_replace("/<span([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/span>/i", "", $data);
        $data = preg_replace("/<h1([^>]+)?>.*<\/h1>/i", "", $data);
        $data = preg_replace("/<h2([^>]+)?>.*<\/h2>/i", "", $data);
        $data = preg_replace("/<font([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/font>/i", "", $data);
        $data = preg_replace("/<center([^>]+)?>/i", "", $data);
        $data = preg_replace("/<\/center>/i", "", $data);
        $data = preg_replace("/<b ([^>]+)?>.*?<\/b>/i", "", $data);
        $data = preg_replace("/<a [^>]*>.*?<\/a>/i", "", $data);
        $data = preg_replace("/&nbsp;/i", "", $data);
        $data = preg_replace("/<br\/>/i", "", $data);
        return $data;
    }

    /**
     * 通过正则取括号里的内容.
     */
    public static function getPregData($data)
    {
        preg_match('/^.+?\((.+?)\).*$/', $data, $result);
        return isset($result[1]) ? $result[1] : '';
    }

    /**
     * 读取Excel数据.
     *
     * @param $file
     * @param $startRow
     *
     * @return array
     */
    public static function getExcelData($file, $startRow = 2)
    {
        require_once("phpexcel/PHPExcel/IOFactory.php");
        require_once("phpexcel/PHPExcel/Cell.php");

        $data = [];

        $PHPExcel = \PHPExcel_IOFactory::createReader('Excel2007')->load($file);
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数

        // 循环读取每个单元格的数据
        for ($row = $startRow; $row <= $highestRow; $row++) {//行数是以第1行开始
            for ($column = 'A'; $column <= $highestColumn; $column++) {//列数是以A列开始
                $value = $sheet->getCell($column . $row)->getValue();
                $data[$row][] = trim($value);
            }
        }

        return $data;
    }
    /**
     * 简单对称加密算法之加密
     * @param String $string 需要加密的字串
     * @param String $skey 加密EKY
     * @date 2013-08-13 19:30
     * @update 2014-10-10 10:10
     * @return String
     */
    public static function encode($string = '', $skey = 'cxphp') {
        $strArr = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value)
            $key < $strCount && $strArr[$key].=$value;
        return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
    }
    /**
     * 简单对称加密算法之解密
     * @param String $string 需要解密的字串
     * @param String $skey 解密KEY
     * @date 2013-08-13 19:30
     * @update 2014-10-10 10:10
     * @return String
     */
    public static function decode($string = '', $skey = 'cxphp') {
        $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value)
            $key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
        return base64_decode(join('', $strArr));
    }

    /**
     * 检查textarea的行数.
     *
     * @param $string
     * @param $count
     * @return bool
     */
    public static function checkAreaTextCount($string, $count)
    {
        $array = explode("\r\n", $string);
        if (!empty($array) && count($array) > $count) {
            return false;
        }
        return true;
    }

    /**
     * 获取公共文件域名.
     *
     * @return string
     */
    public static function getPublicFilePath()
    {
        return "http://p12.jmstatic.com";
    }

    /**
     * 季度开始日期
     *
     * @return string
     */
    public static function getQuarterStartDate()
    {
        $season = ceil(date('n') / 3);
        return date('Y-m-d', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
    }

    /**
     * 计算输入汉字个数 2个英文字符算1个汉字.
     *
     * @param $input
     * @param string $charset
     * @return float|int
     */
    public static function getStringLength($input, $charset = 'UTF-8')
    {
        if (empty($input)) {
            return 0;
        }
        $strLength = strlen($input);
        $mbStrLength = mb_strlen($input, $charset);

        // 汉字个数
        $chineseNum = ($strLength - $mbStrLength) / 2;
        // 英文字符数
        $englishNum = $mbStrLength - $chineseNum;

        $result = $chineseNum + ceil($englishNum / 2);

        return $result;
    }

    /**
     * 验证是否手机请求
     * @return bool
     */
    public static function isMobileRequest() {
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
        $mobile_browser = '0';
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
        }
        if ((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false)) {
            $mobile_browser++;
        }
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            $mobile_browser++;
        }
        if (isset($_SERVER['HTTP_PROFILE'])) {
            $mobile_browser++;
        }
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda', 'xda-'
        );
        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }
        if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false) {
            $mobile_browser++;
        }
        // Pre-final check to reset everything if the user is on Windows
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false) {
            $mobile_browser = 0;
        }
        // But WP7 is also Windows, with a slightly different characteristic
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false) {
            $mobile_browser++;
        }
        if ($mobile_browser > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证是否为纯数字
     *
     * @param $num
     * @return bool
     */
    public static function isNumber($num) {
        if ( !is_numeric($num)) {
            return false;
        }
        if (!preg_match('/^[1-9][0-9]*/', (string)$num)) {
            return false;
        }
        $num = strval($num);
        return ctype_digit($num);
    }

    /**
     * 按字符串长度分割成数组.
     *
     * @param $string
     * @param int $length
     * @return array
     */
    public static function mbStrSplit($string, $length = 1)
    {
        $start = 0;
        $array = [];
        $strLength = mb_strlen($string);
        while($strLength){
            $array[] = mb_substr($string, $start, $length, 'utf8');
            $string = mb_substr($string, $length, $strLength, 'utf8');
            $strLength = mb_strlen($string);
        }
        return $array;
    }

    /**
     * 获取扩展字段的数组形式.
     *
     * @param string $note 数据库中的扩展字段字符.
     *
     * @return array
     */
    public static function getNoteField($note)
    {
        $result = array();
        if (!empty($note) && is_scalar($note)) {
            $matches = array();
            preg_match_all("/([^:]+):(.*)\n/", rtrim($note) . "\n", $matches);
            foreach ($matches[1] as $k => $v) {
                $result[$v] = $matches[2][$k];
            }
            $result = str_replace(array('%%', '%n'), array("%", "\n"), $result);
        } elseif (is_array($note)) {
            $result = $note;
        }
        return $result;
    }
    
}