<?php

/*
 * 对于能够确定“即使重试也无法成功”的请求，
 * 直接标记为失败，然后继续处理下一个请求
 */

class FatalException extends CException
{
}
