<?php
namespace Org\Util;
use OSS\OssClient;
use OSS\Core\OssException;

//oss
require_once './ThinkPHP/Library/Vendor/oss/autoload.php';
/**
 * oss方法
 */
class Oss{
	private $accessKeyId = '';
	private $accessKeySecret = '';
	private $endpoint = '';
	
	public function __construct($accessKeyId,$accessKeySecret,$endpoint){
		$this->accessKeyId = $accessKeyId;
		$this->accessKeySecret = $accessKeySecret;
		$this->endpoint = $endpoint;	
	}
	
	/**
	 * 上传指定的本地文件内容
	 *
	 * @param OssClient $ossClient OSSClient实例
	 * @param string $bucket 存储空间名称
	 * @return null
	 */
	function uploadFile($bucket,$file)
	{
		$object = "shop/$file";
		//$object_desc = "thumb/$file";
		
		$filePath = './Public/upfile/'.$file;
		//$filePath_desc = './Public/thumb/'.$file;
		
		//echo $filePath;
		try {
			$ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);		
			$ossClient->setTimeout(3600); //setTimeout设置请求超时时间，单位秒，默认是5184000秒, 这里建议 不要设置太小，如果上传文件很大，消耗的时间会比较长
			$ossClient->setConnectTimeout(10); //setConnectTimeout设置连接超时时间，单位秒，默认是10秒
			try{
				$ossClient->uploadFile($bucket, $object, $filePath);
				//$ossClient->uploadFile($bucket, $object_desc, $filePath_desc);
				return 1;
			} catch(OssException $e) {
				//printf(__FUNCTION__ . ": FAILED\n");
				json('400','失败',$e->getMessage());
				return;
			}
			print(__FUNCTION__ . ": OK" . "\n");
		} catch (OssException $e) {
			json('400','失败',$e->getMessage());
		}	
	}

	
	
	
}