<?php
namespace library\base;


class ControllerBase extends \Yaf\Controller_Abstract{

    protected $request;
    protected $authToken = NULL;
    protected $uuid = NULL;
    protected static $post = [];

    public function init()
    {
        $this->request       = $this->getRequest();
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->authToken = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (isset($_SERVER['HTTP_UUID'])) {
            $this->uuid      = $_SERVER['HTTP_UUID'];
        }
    }

    /**
     * @param int $errno
     * @param string $errmsg
     * @param string $data
     * @param bool $showJson
     * @return array|void
     */
    protected function returnResult($errno = 0, $errmsg = '', $data = '', $showJson = true)
    {
        header('Content-Type:application/json');

        //$errmsg = ErrorCode::getErrMsg($errno);
        $result = [
            'errno' => $errno,
            'errmsg' => $errmsg,
            'data' => $data,
        ];

        if (!$showJson) {
            return $result;
        }
        echo json_encode($result);
        return;
    }

    /**
     * @desc son
     * @param array $data
     * @param bool $show
     * @return null
     */
    protected function returnPureJson($data = null, $show = true)
    {
        header('Content-Type:application/json');
        $json = json_encode($data);
        if ($show) {
            echo $json;
        } else {
            return $json;
        }
    }

    protected function returnResultCache($errno = 0, $errmsg = '', $data = '', $lifeTime = 0)
    {
        header('Content-Type:application/json');
        $result = [
            'errno' => intval($errno),
            'errmsg' => $errmsg,
            'data' => $data,
        ];

        if ($lifeTime) {
            $seconds_to_cache = gmdate("D, d M Y H:i:s", time() + $lifeTime) . " GMT";
            header("Expires: $seconds_to_cache");
            header("Pragma: cache");
            header("Cache-Control: public");
            header("Cache-Control: max-age=$seconds_to_cache");
            header("Last-Modified: $seconds_to_cache");
        } else {
            header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0"); // HTTP/1.1
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
            header("Pragma: no-cache"); // Date in the past
        }
        echo json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * @param null $data
     * @param int $lifeTime
     * @param bool $show
     * @return string
     */
    protected function returnPureJsonCache($data = null, $lifeTime = 0, $show = true)
    {
        header('Content-Type:application/json');

        if ($lifeTime) {
            $seconds_to_cache = gmdate("D, d M Y H:i:s", time() + $lifeTime) . " GMT";
            header("Expires: $seconds_to_cache");
            header("Pragma: cache");
            header("Cache-Control: public");
            header("Cache-Control: max-age=$seconds_to_cache");
            header("Last-Modified: $seconds_to_cache");
        } else {
            header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0"); // HTTP/1.1
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
            header("Pragma: no-cache"); // Date in the past
        }

        $json = json_encode($data);
        if ($show) {
            echo $json;
        } else {
            return $json;
        }
    }

    /**
     *
     * @param  [type]  $name [descrip
     * tion]
     * @param  boolean $tag  [description]
     * @return [type]        [description]
     */
    public function getParams($name, $tag = false) {
        if ($this->getRequest()->isPost()) {
            $val = $this->getRequest()->getPost($name);
        } elseif (!in_array(str_replace('Action', '', $this->getRequest()->getActionName()),
            static::$post)) {
            $val = $this->getRequest()->getQuery($name);
        } else {
            $val = '';
        }

        if (!empty($val)) {
            if (is_array($val)) {
                foreach ($val as $key => $param) {
                    $val[$key] = $this->_filterParam($param);
                }
            } else {
                $val = $this->_filterParam($val);
            }
        }

        return $val;
    }

    /**
     *
     * @param  string  $value [description]
     * @param  boolean $tag   [description]
     * @return string         [description]
     */
    private function _filterParam($value, $tag = false) {
        if ($tag) {
            $value = trim($value);
        } else {
            $value = trim(strip_tags($value));
        }
        return $value;
    }
}
