<?php

namespace app\forms\api\video_number;

use app\models\Model;

class CommonStyle extends Model
{
    private $data;

    public function getStyle($type, $data)
    {
        $this->data = $data;

        switch ($type) {
            case 1:
                $content = $this->style1();
                break;
            case 2:
                $content = $this->style2();
                break;
            case 3:
                $content = $this->style3();
                break;
            case 4:
                $content = $this->style4();
                break;
            default:
                # code...
                break;
        }

        $content = str_replace(array("\r\n", "\r", "\n"), "", $content);
        $content=preg_replace("/\s+/",' ',$content);

        return $content;
    }

    public function style1()
    {
        $content = "<section style='margin: 0 16px'>
            <section style='display: flex;align-items: center;height: 15px;'>
                <section style='display:flex;width: 15px;height: 15px;'>
                    <img src='{$this->data['shop_icon']}'>
                </section>
                <section style='margin-left:7px;font-size: 12px;color: rgb(53,53,53);width: 326px;display: inline-block;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'>".\Yii::$app->mall->name."</section>
            </section>
            <section style='font-size: 25px;color: rgb(53,53,53); width: 326px;margin: 16px 0;display: inline-block;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'>{$this->data['name']}</section>
            <section style='font-size: 12px;color: rgb(153,153,153);overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 3;-webkit-box-orient: vertical'>{$this->data['subtitle']}</section>
            <section style='margin: 16px 0;display: flex;'>
                <section style='font-size: 16px;color: rgb(".$this->data['theme_color'].");margin-right: 10px;'>￥{$this->data['price']}</section>
                <section style='font-size: 12px;color: rgb(153,153,153);display: flex;align-items: flex-end;text-decoration: line-through;'>￥{$this->data['original_price']}</section>
            </section>
            <section style='width: 100%;max-height: 686px;'>
                <img src='{$this->data['cover_pic']}'>
            </section>
        </section>
        <section style='border-top: 1px solid rgb(226,226,226);margin: 10px 0;'>
            <a class='link' style='text-decoration: none;' data-miniprogram-appid=" . $this->data['app_id'] ." data-miniprogram-path=".$this->data['page_url']." href=''>
            <section style='height: 35px;margin:10px 16px 0;display: flex;align-items: center;justify-content: center;border-radius: 20px;color: rgb(255,255,255);font-size: 14px;background-image:linear-gradient(to right, rgba(".$this->data['theme_color'].",0.7), rgba(".$this->data['theme_color'].",1))'>点击去小程序购买</section>
            </a>
        </section>";

        return $content;
    }

    public function style2()
    {
        $content = "<section style='display: flex;flex-direction: column;align-items: center;'>
            <section style='width:375px;max-height:375px;border-bottom-left-radius: 20px;border-bottom-right-radius: 20px; overflow: hidden;'>
                <img src='{$this->data['cover_pic']}'> 
            </section>
            <section style='font-size: 16px;color: rgb(51,51,51); width: 323.5px;margin: 20px 0 15px;display: inline-block;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;text-align: center;'>{$this->data['name']}
            </section>
            <section style='font-size: 28px;color: rgb(".$this->data['theme_color'].");'>￥{$this->data['price']}</section>
            <section style='font-size: 16px;color: rgb(153,153,153);text-decoration: line-through;'>￥{$this->data['original_price']}
            </section>
            <section style='margin: 16px 16px 0;font-size: 13px;color: rgb(153,153,153);overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 3;-webkit-box-orient: vertical;position: relative;'>
                {$this->data['subtitle']}
            </section>
        </section>
        <section>
            <a class='link' style='text-decoration: none;' data-miniprogram-appid=" . $this->data['app_id'] ." data-miniprogram-path=" . $this->data['page_url'] ." href=''>
            <section style='height: 44px;margin:20px 32px 0;display: flex;align-items: center;justify-content: center;border-radius: 25px;color: rgb(255,255,255);font-size: 14px;background: rgb(".$this->data['theme_color'].")'>点击去小程序购买</section>
            </a>
        </section>";

        return $content;
    }

    public function style3()
    {
        $content = "<section style='width: 100%;background: rgb(39,38,44);'>
            <section style='padding: 31px 16px;'>
                <section style='font-size: 24px;width: 100%;color: rgb(255,255,255);display: inline-block;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'>
                    {$this->data['name']}
                </section>
                <section style='margin: 16px 0;font-size: 12px;color: rgb(153,153,153);overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 3;-webkit-box-orient: vertical;'>
                    {$this->data['subtitle']}
                </section>
                <section style='display: flex;align-items: center;'>
                    <section style='font-size: 28px;color: rgb(".$this->data['theme_color'].");'>
                        ￥{$this->data['price']}
                    </section>
                    <section style='font-size: 16px;color: rgb(153,153,153);margin-left: 12px;flex-grow: 1;text-decoration: line-through;'>
                        ￥{$this->data['original_price']}
                    </section>
                    <section>
                        <a class='link' style='text-decoration: none;' data-miniprogram-appid=".$this->data['app_id']." data-miniprogram-path=".$this->data['page_url']." href=''>
                        <section style='width: 108.5px;height: 33.5px;display: flex;align-items: center;justify-content: center;border-radius: 25px;color: rgb(255,255,255);font-size: 15px;background: rgb(".$this->data['theme_color'].")'>点击购买</section>
                        </a>
                    </section>
                </section>
            </section>
            <section style='border-top-left-radius: 20px;border-top-right-radius: 20px; overflow: hidden;background: rgb(255,255,255);padding: 16px;'>
                <section style='display: flex;margin-bottom: 16px;align-items: center;'>
                    <section style='width: 44px;height: 44px;'>
                        <img src='{$this->data['shop_icon']}'>
                    </section>
                    <section style='margin-left: 13px;color: rgb(53,53,53);display: inline-block;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;'>".\Yii::$app->mall->name."</section>
                </section>
                <section style='width: 100%;max-height:343px;'>
                    <img src='{$this->data['cover_pic']}'> 
                </section>
            </section>
        </section>";

        return $content;
    }

    public function style4()
    {
        $pageUrl = 'pages/index/index?user_id' . \Yii::$app->user->id;
        $string = '';
        $num = count($this->data['pic_list']) >= 4 ? 4 : count($this->data['pic_list']);
        $width = $num ? 100 / $num : 100;
        foreach ($this->data['pic_list'] as $index => $url) {
            if ($index < 4) {
                $string .= "<section style='display: inline-block;vertical-align: top;width: ".$width."%;box-sizing: border-box;' powered-by='xiumi.us'>
            <section style='text-align: center;margin-top: 10px;margin-bottom: 10px;width: 100%;box-sizing: border-box;'>
                    <section style='max-width: 100%;max-height:375px;vertical-align: middle;display: inline-block;line-height: 0;width: 100%;height:375px;box-sizing: border-box;background-size:100% 100%;background-image:url(".$url.")'>
                    </section>
                </section>
            </section>";
            }
        }
        $content = "<section style='width: 100%;background: rgb(247,247,247);'>
            <section style='padding: 16px;'>
                <section style='display: flex;align-items: center;'>
                    <section style='width: 44px;height: 44px;'>
                        <img src='{$this->data['shop_icon']}'>
                    </section>
                    <section style='margin-left: 13px;color: rgb(53,53,53);display: inline-block;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;flex-grow: 1;'>
                        ".\Yii::$app->mall->name."
                    </section>
                    <section>
                        <a class='link' style='text-decoration: none;' data-miniprogram-appid=".$this->data['app_id']." data-miniprogram-path=".$pageUrl." href=''>
                        <section style='width: 108.5px;height: 33.5px;display: flex;align-items: center;justify-content: center;border-radius: 25px;color: rgb(255,255,255);font-size: 15px;background: rgb(".$this->data['theme_color'].")'>进店逛逛</section>
                        </a>
                    </section>
                </section>
            </section>
            <section style='box-sizing: border-box;font-size: 16px;'>
                <section style='box-sizing: border-box;' powered-by='xiumi.us'>
                    <section style='display: inline-block;width: 100%;vertical-align: top;overflow-x: auto;box-sizing: border-box;'>
                        <section style='overflow: hidden;width: ".($num * 100)."%;transform: rotate(0deg);-webkit-transform: rotate(0deg);-moz-transform: rotate(0deg);-o-transform: rotate(0deg);max-width: 400% !important;box-sizing: border-box;'>".$string."
                        </section>
                    </section>
                </section>
            </section>
            <section style='padding: 32px 16px;display: flex;flex-direction: column;align-items: center;'>
                <section style='font-size: 16px;width: 100%;color: rgb(12,12,12);display: inline-block;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;text-align: center;'>
                    {$this->data['name']}
                </section>
                <section style='font-size: 16px;color: rgb(".$this->data['theme_color'].");margin: 16px 0'>￥{$this->data['price']}</section>
                <section style='font-size: 13px;color: rgb(151,151,151);overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 3;-webkit-box-orient: vertical;'>{$this->data['subtitle']}
                </section>
                <section>
                <a class='link' style='text-decoration: none;' data-miniprogram-appid=" . $this->data['app_id'] ." data-miniprogram-path=" . $this->data['page_url'] ." href=''>
                <section style='width: 108.5px;height: 33.5px;margin:20px 32px 0;display: flex;align-items: center;justify-content: center;border-radius: 25px;color: rgb(255,255,255);font-size: 14px;background: rgb(".$this->data['theme_color'].")'>点击购买</section>
                </a>
            </section>
            </section>
        </section>";

        return $content;
    }
}
