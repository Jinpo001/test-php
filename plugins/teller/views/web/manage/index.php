<?php
Yii::$app->loadViewComponent('app-number-input');
Yii::$app->loadViewComponent('teller/teller-head');
Yii::$app->loadViewComponent('teller/teller-bottom');
Yii::$app->loadViewComponent('teller/teller-goods');
Yii::$app->loadViewComponent('teller/teller-order');
$permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
$is_plugin_show = array_search('pond', $permission) !== false || array_search('scratch', $permission) !== false;
?>
<style>
	.main-view.is_pad .main-center .tab {
		height: 40px;
		font-size: 17px;
	}
	.main-view.is_pad .main-center .tab img {
		width: 16px;
		height: 14px;
	}
	.main-view.is_pad .main-center .tab .tab-item {
		width: 90px;
		font-size: 14px;
	}
	.main-view.is_pad .goods-view {
		padding-top: 40px;
		padding-left: 8px;
	}
	.main-view.is_pad .el-button {
		padding-top: 0!important;
		padding-bottom: 0!important;
		font-size: 13px!important;
		max-height: 36px!important;
		line-height: 36px!important;
	}
	.main-view.is_pad .input-item {
		top: 40px;
		padding-left: 8px;
	}
	.main-view.is_pad .input-item.other {
		padding-left: 0;
	}
	.main-view.is_pad .input-item .input-area {
		height: 28px;
	}
	.main-view.is_pad .input-item .el-input__inner {
		height: 36px;
		font-size: 15px;
	}
	.main-view.is_pad .choose-goods-title {
		top: 50px;
		height: 40px;
		line-height: 40px;
		font-size: 14px;
	}
	.main-view.is_pad .choose-goods-title.have-sale {
		top: 8px;
	}
	.main-view.is_pad .add-credit-type {
		width: 227px;
		height: 77px;
		margin-top: 0;
		margin-bottom: 10px;
		font-size: 13px;
	}
	.main-view.is_pad .choose-goods-info .add-credit-type .active {
		width: 16px;
		height: 16px;
		margin-right: 0;
	}
	.main-view.is_pad .add-credit-type img {
		width: 46px;
		height: 46px;
		margin-right: 20px;
	}
	.main-view.is_pad .member-icon {
		width: 80px;
	}
	.main-view.is_pad .no-margin {
		margin-top: 0!important;
	}
	.main-view.is_pad .choose-goods-info {
		padding-top: 3%;
	}
	.main-view.is_pad .goods-cat-item {
		font-size: 18px;
	}
	.main-view.is_pad .menu-goods-item {
		height: 60px;
		font-size: 11px;
	}
	.main-view.is_pad .menu-goods-item .menu-goods-info {
		overflow: hidden;
	}
	.main-view.is_pad .menu-goods-item .el-image {
		height: 60px;
		width: 60px;
		margin-right: 10px;
	}
	.main-view.is_pad .menu-goods-list .menu-goods-item .menu-goods-name {
		font-size: 14px;
	}
	.main-view.is_pad .menu-goods-list .menu-goods-item .menu-goods-price {
		font-size: 14px;
		margin-top: 1px;
	}
	.main-view.is_pad .menu-goods-list .menu-goods-item .menu-goods-number img {
		width: 22px;
		height: 22px;
	}
	.main-view.is_pad .choose-goods>.choose-goods-info {
		padding: 40px 6px;
	}
	.main-view.is_pad .choose-goods>.choose-goods-info.attr-view {
		padding-top: 50px;
		padding-bottom: 90px;
	}
	.main-view.is_pad .choose-goods>.choose-goods-info.order-list {
		padding-top: 103px;
	}
	.main-view.is_pad .detail-goods-attr.detail-goods-number {
		padding-right: 50%;
		left: 0;
		padding-left: 24px;
		padding-bottom: 20px;
	}
	.main-view.is_pad .next-step {
		width: 146px;
		height: 34px;
		line-height: 34px;
		font-size: 16px;
	}
	.main-view.is_pad .pay-list {
		width: 570px;
		margin: 1% 0;
		margin-right: 0;
		display: flex;
		flex-wrap: wrap;
	}
	.main-view.is_pad .pay-type {
		width: 182px;
		height: 123px;
		border-radius: 16px;
		background-color: #f5f9fc;
		font-size: 16px;
		margin: 4px;
		color: #353535;
		cursor: pointer;
	}
	.main-view.is_pad .money-show {
		padding: 6px 20px;
		width: 60%;
	}
	.main-view.is_pad .money-show .total-money-show span {
		font-size: 36px;
	}
	.main-view.is_pad .money-show+.money-show-img {
		margin: 12px auto;
		width: 96.6px;
	}
	.main-view.is_pad .barcode-input.el-input input {
		height: 32px;
		line-height: 32px;
		font-size: 16px;
	}
	.main-view.is_pad .balance-pay-type {
		position: relative;
	}
	.main-view.is_pad .balance-pay-type .next-step.absolute {
		bottom: -30px;
	}
	.main-view {
		position: fixed;
		top: 0;
		left: 0;
		z-index: 9;
		height: 100%;
		width: 100%;
	}
	.main-view.other {
		background-color: #d7efff;
		padding: 20px;
	}
	.main-view .goods-cat {
		flex-shrink: 0;
		height: 100%;
		width: 8.78%;
		max-width: 174px;
		background-color: #f4f8fb;
		overflow-y: auto;
	}
	.main-view .goods-cat::-webkit-scrollbar { 
		width: 0 !important
	}
	.main-view .goods-cat {
		-ms-overflow-style: none;
	}
	.main-view .goods-cat {
		overflow: -moz-scrollbars-none;
	}
	.goods-cat-item {
		height: 84px;
		line-height: 84px;
		text-align: center;
		font-size: 20px;
		cursor: pointer;
		background-color: #f4f8fb;
		color: #666666;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
	.goods-cat-item.active,.goods-cat-item.last-active+div {
		background-color: #d7efff;
		font-weight: 600;
		color: #353535;
		position: relative;
	}
	.goods-cat-item.active+div,.goods-cat-item.last-active+div+div {
		border-top-right-radius: 16px;
	}
	.goods-cat-item.last-active {
		border-bottom-right-radius: 16px;
	}
	.goods-cat-item.active .active-line,.goods-cat-item.last-active+div .active-line {
		width: 5px;
		height: 44px;
		border-top-right-radius: 8px;
		border-bottom-right-radius: 8px;
		background-color: #3399ff;
		position: absolute;
		left: 0;
		top: 22px;
	}
	.main-center {
		background-color: #d7efff;
		border-top-right-radius: 16px;
		position: relative;
		min-width: 528px;
	}
	.main-center.padding {
		padding: 0 15px;
	}
	.main-view.is_pad .main-center.padding {
		padding: 0 10px;
	}
	.main-center .tab {
		background-color: #f4f8fb;
		border-top-left-radius: 16px;
		border-top-right-radius: 16px;
		height: 74px;
		position: absolute;
		font-size: 18px;
		top: 0;
		left: 0;
		width: 100%;
		z-index: 10;
	}
	.main-center .tab .tab-item {
		width: 156px;
		color: #adb2b8;
		background-color: #d7efff;
		cursor: pointer;
	}
	.main-center .tab .tab-item>div {
		height: 100%;
		background-color: #f4f8fb;
	}
	.main-center .tab .tab-item.active {
		background-color: #f4f8fb;
		border-top-left-radius: 16px;
		border-top-right-radius: 16px;
	}
	.main-center .tab .tab-item.active>div {
		background-color: #d7efff;
		border-top-left-radius: 16px;
		border-top-right-radius: 16px;
	}
	.main-center .tab .tab-item.active+div>div {
		border-bottom-left-radius: 16px;
	}
	.main-center .tab .tab-item.last-active>div {
		border-bottom-right-radius: 16px;
	}
	.main-center .tab img {
		height: 24px;
		width: 26px;
		margin-right: 10px;
	}
	.goods-view {
		padding-top: 74px;
		padding-left: 35px;
		height: 100%;
		position: relative;
	}
	.input-item {
		position: absolute;
		z-index: 10;
		top: 74px;
		left: 0;
		width: 98%;
		padding-left: 35px;
		background-color: #d7efff;
	}
	.input-item .input-area {
		width: 435px;
		height: 46px;
		margin-top: 15px;
		margin-bottom: 10px;
	}
	.input-item .full-reduce {
		padding: 12px 15px;
		margin-top: 15px;
		font-size: 15px;
		border-radius: 8px;
		background-color: #e9f6ff;
		color: #3399ff;
	}
	.input-item .el-input__inner {
		border: 0;
		height: 46px;
		border-top-left-radius: 16px;
		border-bottom-left-radius: 16px;
		font-size: 18px;
	}
	.input-item.other {
		background-color: #fff;
		left: 0;
		padding-left: 17px;
		width: 90%;
	}
	.input-item.other .el-input__inner {
		background-color: #f4f8fb;
	}
	.input-item .el-input__inner:hover{
		outline: 0;
	}
	.input-item .el-input__inner:focus{
		outline: 0;
	}
	.input-item .el-input-group__append {
		background-color: #fff;
		border: 0;
		width: 10%;
		padding: 0;
		border-top-right-radius: 16px;
		border-bottom-right-radius: 16px;
	}
	.input-item.other .el-input-group__append {
		background-color: #f4f8fb;
	}
	.input-item .el-input-group__append .el-button,.coupon-input .el-input-group__append .el-button {
		padding: 0;
		margin: 0;
	}
	.coupon-input {
		display: inline-block;
		width: 285px;
		margin: 20px 0;
	}

	.coupon-input .el-input__inner {
		border-right: 0;
	}

	.coupon-input .el-input__inner:hover{
		border: 1px solid #dcdfe6;
		border-right: 0;
		outline: 0;
	}

	.coupon-input .el-input__inner:focus{
		border: 1px solid #dcdfe6;
		border-right: 0;
		outline: 0;
	}

	.coupon-input .el-input-group__append {
		background-color: #fff;
		border-left: 0;
		width: 10%;
		padding: 0;
	}
	.menu {
		position: relative;
		flex-shrink: 0;
		height: 100%;
		z-index: 8;
		width: 32.91%;
		max-width: 554px;
		background-color: #f8fcff;
	}
	.menu-list {
		background-color: #f8fcff;
		height: 100%;
	}
	.menu-title {
		background-color: #fff;
	}
	.menu-title .el-tag {
		background-color: #f4f8fb;
	}
	.menu-title>div {
		border-top-left-radius: 16px;
		height: 48px;
		line-height: 48px;
		padding-left: 32px;
		background-color: #f8fcff;
	}
	.menu-goods-list {
		padding: 0 22px;
		background-color: #f8fcff;
		height: 75%;
		overflow-y: auto;
		width: 100%;
	}
	.menu-goods-list .menu-goods-item {
		height: 88px;
		border-radius: 16px;
		background-color: #fff;
		width: 100%;
		color: #a9a9a9;
		font-size: 12px;
		margin-bottom: 10px;
	}
	.menu-goods-list .menu-goods-item .el-image {
		border-radius: 16px;
		width: 88px;
		height: 88px;
		margin-right: 20px;
		flex-shrink: 0;
	}
	.menu-goods-list .menu-goods-item .menu-goods-info {
		width: 100%;
		position: relative;
		overflow: hidden;
	}
	.menu-goods-list .menu-goods-item .menu-goods-name {
		color: #353535;
		margin-bottom: 3px;
		font-size: 15px;
		width: 60%;
		max-width: 300px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.menu-goods-list .menu-goods-item .menu-goods-price {
		color: #ff4544 ;
		margin-top: 3px;
		font-size: 16px;
	}
	.menu-goods-list .menu-goods-item .menu-goods-number {
		position: absolute;
		bottom: 8px;
		right: 0;
		font-size: 14px;
		color: #353535;
	}
	.menu-goods-list .menu-goods-item .menu-goods-number img {
		margin: 0 8px;
		width: 36px;
		height: 36px;
		display: block;
		cursor: pointer;
	}
	.menu-bottom {
		position: absolute;
		bottom: 0;
		left: 22px;
		right: 22px;
		border-top-right-radius: 16px;
		border-top-left-radius: 16px;
		background-color: #fff;
		height: 176px;
		padding: 20px 12px;
		font-size: 15px;
	}
	.menu-bottom.pad-menu-bottom {
		height: 15%;
		font-size: 13px;
		padding: 1% 12px;
		left: 0;
		width: 100%;
	}
	.menu-bottom.pad-menu-bottom .discount .el-button {
		height: 20px!important;
		line-height: 20px!important;
	}
	.menu-bottom.pad-menu-bottom .price {
		font-size: 17px;
		margin-bottom: 3px;
		margin-top: 0;
	}
	.menu-bottom.pad-menu-bottom .price>div:last-of-type {
		font-size: 20px!important;
	}
	.menu-bottom .discount-price {
		font-size: 15px!important;
		color: #ff4544;
	}
	.menu-bottom .discount .el-button {
		background-color: #e6f5ff;
		color: #007fff;
		margin-left: 12px;
		height: 26px;
		width: 48px;
		padding: 0 11px;
		border: 0;
	}
	.menu-bottom .price {
		font-size: 19px;
		margin-bottom: 15px;
		margin-top: 5px;
	}
	.menu-button.fixed {
		position: absolute;
		bottom: 0;
		left: 0;
		width: 100%;
	    padding: 34px 5%;
	    background-color: #fff;
	    z-index: 10;
	    border-bottom-left-radius: 16px;
	    border-bottom-right-radius: 16px;
	}
	.menu-button.fixed .money {
		margin-left: 20px;
	}
	.menu-button .el-button {
		width: 234px;
		height: 53px;
		border-radius: 27px;
		font-size: 18px;
	}
	.menu-button .el-button.money {
		background: linear-gradient(to right, #05a8ff, #007fff);
		color: #fff;
	}
	.choose-goods {
		padding: 90px 0 8.59% 0;
		width: 100%;
		height: 100%;
		position: relative;
		border-top-left-radius: 16px;
	}
	.choose-goods>.choose-goods-info {
		background-color: #fff;
		padding: 0 20px;
		height: 100%;
		overflow: auto;
		padding-top: 6%;
		border-radius: 16px;
	}
	.choose-goods>.choose-goods-info.member {
		padding-left: 10%;
		padding-top: 4%;
	}
	.choose-goods>.choose-goods-info.order-list {
		padding-top: 120px;
		padding-bottom: 0;
		height: 100%
	}
	.choose-goods>.choose-goods-info.member .member-login {
		width: 234px;
		line-height: 53px;
		text-align: center;
		margin: 70px auto;
		height: 53px;
		border-radius: 27px;
		font-size: 18px;
		cursor: pointer;
		background-color: #e9f6ff;
		color: #3399ff;
	}
	.choose-goods .return-btn {
		width: 110px;
		height: 100%;
		background-color: #f4f8fb;
		border-top-left-radius: 16px;
		border-bottom-right-radius: 16px;
		font-size: 16px;
		color: #353535;
		cursor: pointer;
		margin-right: 15px;
	}
	.choose-goods .choose-goods-title {
		position: absolute;
		left: 0;
		top: 90px;
		width: 100%;
		z-index: 4;
		height: 50px;
		line-height: 50px;
		font-size: 16px;
		background-color: #fff;
		border-top-left-radius: 16px;
		border-top-right-radius: 16px;
	}
	.detail-goods {
		height: 130px;
	}
	.detail-goods .el-image {
		width: 130px;
		height: 130px;
		border-radius: 16px;
		margin-right: 20px;
		flex-shrink: 0;
	}
	.detail-goods .detail-goods-info {
		font-size: 16px;
		color: #353535;
		flex-grow: 1;
	}
	.detail-goods .detail-goods-info .detail-goods-name {
		height: 60px;
		line-height: 30px;
		width: 70%;
		margin-bottom: 10px;
		word-break: break-all;
		text-overflow: ellipsis;
		display: -webkit-box;
		-webkit-box-orient: vertical;
		-webkit-line-clamp: 2;
		overflow: hidden;
		white-space: normal !important;
	}
	.detail-goods-attr .el-button.el-button--info {
		color: #353535;
		background-color: #f5f9fc;
		border: 0;
	}
	.detail-goods-attr .el-button.el-button--info.is-disabled {
		color: #fff;
	}
	.detail-goods-attr .el-button {
		margin-top: 15px;
	}
	.detail-goods-attr .detail-attr-name {
		color: #959595;
		margin-top: 15px;
	}
	.detail-goods-attr.detail-goods-number {
		position: absolute;
		bottom: 0;
		left: 0;
		padding-left: 24px;
		padding-bottom: 20px;
		z-index: 6;
		width: 100%;
		background-color: #fff;
		padding-right: 70%;
		border-bottom-left-radius: 16px;
		border-bottom-right-radius: 16px;
	}
	.detail-goods-attr.detail-goods-number .el-button {
		margin-top: 0;
	}
	.detail-goods-attr.detail-goods-number .el-input {
		margin: 0 10px;
	}
	.detail-goods-attr.detail-goods-number .el-input input {
		border: 0;
		background-color: #f5f9fc;
		border-radius: 8px;
	}
	.member-icon {
		width: 20%;
		max-width: 102px;
		margin: 5% auto 10px;
	}
	.sales-item {
		width: 220px;
		height: 100px;
		border-radius: 16px;
		padding: 0 16px;
		background-color: #f4f8fb;
		margin-right: 15px;
		color: #353535;
		cursor: pointer;
		margin-top: 16px;
	}
	.sales-item img {
		width: 56px;
		height: 56px;
		border-radius: 50%;
		margin-right: 10px;
	}
	.sales-item.active {
		border: 2px solid #3399ff;
	}
	.sale-info {
		padding: 18px 16px 10px;
		border: 1px solid #ebeef5;
	}
	.sale-info>div {
		margin-bottom: 12px;
	}
	.sale-info>div img {
		width: 56px;
		height: 56px;
		margin-left: 10px;
	}
	.sale-info>div span {
		margin-left: 10px;
	}
	.sale-dialog .el-dialog__body {
		padding-top: 10px;
	}
	.next-step {
		height: 54px;
		line-height: 54px;
		width: 240px;
		border-radius: 27px;
		font-size: 18px;
		text-align: center;
		background: linear-gradient(to right, #05a8ff, #007fff);
		color: #fff;
		cursor: pointer;
	}
	.next-step.absolute {
		position: absolute;
		bottom: 13%;
		right: 5%;
	}
	.other-info {
		position: absolute;
		left: 35px;
		top: 165px;
		width: 25%;
		max-width: 300px;
		z-index: 100;
	}
	.other-info .other-item {
		width: 100%;
		padding: 15px 25px;
		border-radius: 16px;
		background-color: #eaf7ff;
		margin-bottom: 10px;
	}
	.other-info .other-item .other-item-line {
		margin-bottom: 10px;
		width: 100%;
	}
	.other-info .other-item .other-item-line .other-item-label {
		color: #666666;
	}
	.other-info .other-item .other-item-line .other-item-label.member-name {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		width: 100px;
	}

	.pay-list {
		width: 836px;
		margin: 40px 305px;
		margin-right: 0;
		display: flex;
		flex-wrap: wrap;
	}
	.pay-type {
		width: 266px;
		height: 180px;
		border-radius: 16px;
		background-color: #f5f9fc;
		font-size: 18px;
		margin: 6px;
		color: #353535;
		cursor: pointer;
	}
	.pay-type.active {
		background-color: #3399ff!important;
		color: #fff!important;
	}
	.pay-type img {
		width: 56px;
		height: 56px;
		margin-bottom: 15px;
	}
	.tab-nav {
		position: absolute;
		z-index: 100;
	}
	.tab-nav-item {
		width: 125px;
		height: 52px;
		background-color: #fff;
		color: #666666;
		font-size: 16px;
	}
	.tab-nav-item.tab-cashier {
		border-top-left-radius: 16px;
		border-bottom-right-radius: 16px;
		background-color: #3399ff;
		color: #fff;
		cursor: pointer;
	}
	.tab-cashier img {
		width: 18px;
		height: 18px;
		margin-right: 10px;
	}
	.cashier-info {
		background-color: #fff;
		width: 100%;
		height: 100%;
		border-radius: 16px;
		padding-top: 110px;
		position: relative;
	}
	.cashier-avatar {
		width: 88px;
		height: 88px;
		display: block;
		margin: 0 auto 40px;
	}
	.cashier-about {
		width: 20%;
		max-width: 360px;
		margin: 0 auto 40px;
	}
	.cashier-about .el-form-item {
		margin-bottom: 6px;
	}
	.change-password .el-dialog__body {
		padding-bottom: 0;
	}
	.change-password .el-dialog__body .el-form-item {
		margin-bottom: 14px;
	}
	.change-password .el-dialog__body .el-form-item .el-input input {
		background-color: #f4f8fb;
	}
	.success-dialog .el-dialog__header {
		padding: 0;
	}
	.success-dialog .el-dialog__body {
		padding: 35px 20px;
	}
	.hung-btn {
		position: relative;
	}
	.hung {
		position: absolute;
		top: -5px;
		right: -2px;
		padding: 0 5px;
		height: 16px;
		line-height: 16px;
		color: #fff;
		background-color: #ff4544;
		border-radius: 8px;
		font-size: 12px;
	}
	.hung-list {
		background-color: #f5f9fc;
		border-radius: 16px;
		padding: 16px;
		position: relative;
		margin-top: 12px;
	}
	.hung-list .el-tag {
		white-space: normal;
		height: auto;
		word-break: break-all;
	}
    .hung-list .order-info {
        margin-bottom: 16px;
        color: #999999;
    }
    .hung-list .order-info>div {
        margin-right: 46px;
    }
    .hung-list .order-info span {
        color: #353535
    }
	.member-info {
		width: 354px;
		margin: 10% auto 0;
		font-size: 16px;
	}
	.member-info .member-avatar {
		width: 56px;
		height: 56px;
		display: block;
		border-radius: 28px;
		margin: 0 auto 10px;
	}
	.member-info .member-label {
		color: #999999;
		margin-right: 6px;
	}
	.member-info .el-button {
		width: 100%;
		margin-top: 12px;
	}
	.el-button.is-plain {
		border: 0;
	}
	.member-coupon .el-switch__label,.user-coupon .el-switch__label.is-active {
		color: #666;
	}
	.user-list{
		margin-top: 10px;
		width: 100%;
		height: 400px;
		overflow: auto;
	}
	.user-item .el-checkbox-button__inner{
		border: 1px solid #e2e2e2;
		height: 155px;
		width: 120px;
		padding-top: 15px;
		text-align: center;
		margin: 0 20px 20px 0;
		cursor: pointer;
		border-radius: 0!important;
	}
	.user-item.active{
		background-color: #50A0E4;
		color: #fff;
	}
	.user-list .avatar{
		height: 60px;
		width: 60px;
		border-radius: 30px;
	}
	.username{
		margin-top: 10px;
		font-size: 13px;
		overflow:hidden;
		text-overflow:ellipsis;
		white-space:nowrap;
		height: 20px;
		margin-bottom: 6px;
	}

	.coupon-list {
		width: 410px;
		border: 1px solid #e2e2e2;
		padding: 12px 14px;
		padding-right: 0;
		height: 215px;
		overflow-y: auto;
		margin-bottom: 20px;
	}

	.coupon-list-item {
		cursor: pointer;
		width: 379px;
		height: 88px;
		position: relative;
		margin-bottom: 5px;
		border: 1px solid #fff;
	}

	.coupon-list-item .active {
		position: absolute;
		bottom: 0;
		right: 0;
		height: 24px;
		width: 24px;
	}

	.coupon-list-item.active {
		border: 1px solid #3399ff;
	}

	.coupon-list-item img {
		width: 100%;
		height: 100%;
	}

	.coupon-list-item .item-left {
		position: absolute;
		left: 0;
		top: 0;
		height:100%;
		width: 108px;
		text-align: center;
		font-size: 12px;
		color: #fff;
		padding-top: 4px;
	}

	.coupon-list-item .coupon-price {
		font-size: 18px;
		padding-bottom: 8px;
	}

	.coupon-list-item .item-right {
		position: absolute;
		left: 120px;
		top: 10%;
		height: 80%;
		width: 66%;
		color: #6f6e6d;
		font-size: 12px;
	}

	.coupon-list-item .item-name {
		font-size: 15px;
		color: #353535;
	}

	.coupon-list-item .item-right div {
		margin-bottom: 6px;
	}

	.t-omit {
		display: inline-block;
		white-space: nowrap;
		width: 100%;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.cashier-info.get-off .cashier-about {
		position: absolute;
		top: 72px;
		left: 14px;
		padding: 16px 20px;
		border-radius: 16px;
		background-color: #f5f9fc;
		z-index: 10;
	}
	.cashier-info.get-off .cashier-content{
		overflow-y: auto;
		height: 100%;
		padding-bottom: 56px;
	}
	.cashier-info .cashier-title {
		margin-bottom: 12px;
		font-size: 16px;
	}
	.cashier-info .cashier-title img {
		width: 18px;
		height: 18px;
		margin-right: 10px;
	}
	.cashier-info .cashier-about .cashier-form>div {
		margin-top: 8px;
		color: #999999;
	}
	.cashier-info .cashier-about .cashier-form>div span {
		color: #353535;
	}
	.cashier-content {
		padding-left: 390px;
	}
	.cashier-content .cashier-content-item {
		background-color: #f5f9fc;
		border-radius: 16px;
		padding: 16px 20px;
		padding-right: 0;
		margin-bottom: 14px;
		display: inline-block;
	}
	.cashier-content .cashier-info-item {
		width: 218px;
		height: 143px;
		background-color: #fff;
		border-radius: 16px;
		margin-right: 16px;
		font-size: 16px;
		position: relative;
		overflow: hidden;
	}
	.cashier-content .cashier-info-item img {
		width: 34px;
		height: 34px;
		margin-bottom: 10px;
		position: relative;
		z-index: 2;
	}
	.cashier-content .cashier-info-item .cashier-info-label {
		color: #999999;
		margin-bottom: 15px;
		font-size: 13px;
	}
	.cashier-content .cashier-content-item .menu-button .el-button {
		width: 234px;
		height: 53px;
		margin-top: 36px;
		margin-right: 24px;
	}
	.cashier-content .cashier-content-item .cashier-info-item .more {
		position: absolute;
		height: 173px;
		width: 364px;
		border-radius: 50% / 50%;
		top: -131px;
		left: 50%;
		margin-left: -182px;
	}
	.choose-goods-info .money-show {
		border-radius: 16px;
		border: 1px solid #e2e2e2;
		padding: 20px;
		width: 40%;
		margin: 10px auto;
		color: #999999;
		text-align: center;
	}
	.choose-goods-info .money-show .total-money-show {
		color: #ff4544;
		font-size: 20px;
	}
	.choose-goods-info .money-show .total-money-show span {
		font-size: 44px;
	}
	.choose-goods-info .money-show+.money-show-img {
		margin: 40px auto;
		width: 138px;
	}
	.choose-goods-info .change-type {
		font-size: 16px;
		color: #666666;
	}
	.choose-goods-info .change-type div {
		cursor: pointer;
		margin: 0 18px;
		border-bottom: 2px solid #ffffff;
		padding-bottom: 6px;
	}
	.choose-goods-info .change-type .active {
		color: #3399ff;
		border-color: #3399ff;
	}
	.choose-goods-info .add-credit-type {
		width: 326px;
		height: 108px;
		cursor: pointer;
		background-color: #fff;
		border-radius: 16px;
		margin-top: 18px;
		font-size: 15px;
		box-shadow: 1px 1px 4px 4px #ebf7ff;
	}
	.choose-goods-info .add-credit-type img {
		width: 66px;
		height: 66px;
		margin-right: 28px;
	}
	.choose-goods-info .add-credit-type.active {
		position: relative;
		border: 1px solid #3399ff;
	}
	.choose-goods-info .add-credit-type .active {
		position: absolute;
		bottom: -1px;
		right: -1px;
		height: 24px;
		width: 24px;
		margin-right: 0;
	}
	.choose-goods-info .recharge-item {
		cursor: pointer;
		padding: 20px 0;
		border-radius: 16px;
		border: 1px solid #e3e3e3;
		background-color: #fff;
		margin-right: 10px;
		margin-top: 10px;
		text-align: center;
		font-size: 14px;
		color: #999999;
	}
	.choose-goods-info .recharge-item.active {
		border-color: #3399ff;
		background-color: #d7efff;
	}
	.choose-goods-info .recharge-item .recharge-item-name {
		width: 165px;
		height: 47px;
		border-right: 2px dotted #3399ff;
		color: #353535;
		font-size: 16px;
		flex-shrink: 0;
	}
	.choose-goods-info .recharge-item.active .recharge-item-name {
		border-color: #3399ff;
		color: #3399ff;
	}
	.choose-goods-info .recharge-item .recharge-item-content {
		padding: 0 20px;
		color: #353535;
		flex-wrap: wrap;
	}
	.recharge-item-content>span {
		word-break: break-all;
	}
	.recharge-item-content>span::after {
		content:  '、';
	}
	.recharge-item-content>span:last-child::after,.recharge-item-content>.not-important::after {
		content:  none;
	}
	.recharge-item-content>span span {
		color: #3399ff;
	}
	.quick-input {
		cursor: pointer;
		border-radius: 4px;
		padding: 3px 8px;
		background-color: #f4f8fb;
		color: #3399ff;
		border: 1px solid #3399ff;
		margin: 0 8px;
		font-size: 14px;
	}
	.barcode-input {
        width: 370px;
        margin: 20px auto 0;
	}
	.barcode-input.el-input input {
        border: 0;
        background-color: #f5f9fc;
        border-radius: 8px;
        height: 42px;
        font-size: 18px;
        line-height: 42px;
    }
    .order-detail {
    	background-color: #fff;
    	border-radius: 16px;
    	margin-bottom: 12px;
    }
    .order-detail .el-image {
    	width: 110px;
    	height: 110px;
    	margin-right: 16px;
    	border-top-left-radius: 16px;
    	border-bottom-left-radius: 16px;
    }
    .order-detail>div {
    	flex-grow: 1;
    	flex-shrink: 0;
    }
    .order-detail .order-detail-goods-name {
    	width: 50%;
    	padding-right: 5%;
    }
    .refund-detail .el-image {
    	width: 120px;
    	height: 120px;
    	margin-right: 16px;
    	border-radius: 16px;
    }
    .refund-info .refund-label {
    	color: #999999;
    	margin: 20px 0 10px;
    }
    .el-textarea .el-textarea__inner{
        resize: none;
    }
    .refund-info .refund-price-input {
    	width: 280px;
    	margin-right: 12px;
    }
    .refund-info .refund-price-input .el-input__inner {
		background-color: #f4f8fb;
		outline: 0;
		border: 0;
	}
	.main-view.is_pad .other-title {
		height: 31px;
		width: 100%;
		margin-top: 50px;
		position: relative;
		z-index: 100;
	}
/* 	.main-view.is_pad .other-title+.choose-goods .choose-goods-title {
		margin-top: -42px;
	} */
	.main-view.is_pad .other-title+.choose-goods .choose-goods-info.choose-recharge {
		margin-top: 0;
		position: relative;
		height: 95%;
	}
	.main-view.is_pad .other-title>div {
		border-radius: 8px;
		background-color: #eaf7ff;
		padding: 6px 10px 6px 12px;
		font-size: 13px;
		color: #72787b;
		margin-bottom: 6px;
	}
	.main-view.is_pad .other-title>div .el-button {
		height: 20px!important;
		line-height: 20px!important;
		padding: 0 10px;
		font-size: 12px!important;
	}
	.main-view.is_pad .cashier-info {
		margin: 8px;
	}
	.main-view.is_pad .tab-cashier img {
		height: 10px;
		width: 10px;
		margin-right: 4px;
	}
	.main-view.is_pad .tab-nav {
		margin: 8px;
	}
	.main-view.is_pad .tab-nav .tab-nav-item {
		width: 68px;
		height: 29px;
		font-size: 12px;
	}
	.main-view.is_pad .cashier-info.get-off .cashier-about {
		top: 36px;
		left: 10px;
		font-size: 12px;
		padding: 8px 12px;
	}
	.main-view.is_pad .cashier-info.get-off .cashier-about .cashier-title {
		font-size: 14px;
	}
	.main-view.is_pad .cashier-content {
		padding-left: 22%;
	}
	.main-view.is_pad .cashier-content .cashier-info-item {
		width: 117px;
		height: 76px;
		font-size: 14px;
		border-radius: 8px;
		margin-right: 8px;
	}
	.main-view.is_pad .cashier-content .cashier-info-item .more {
		height: 86px;
		width: 182px;
		top: -65px;
		margin-left: -91px;
	}
	.main-view.is_pad .cashier-content .cashier-info-item img {
		width: 17px;
		height: 17px;
		margin-bottom: 5px;
	}
	.main-view.is_pad .cashier-content .cashier-info-item .cashier-info-label {
		margin-bottom: 0;
	}
	.main-view.is_pad .cashier-content .cashier-content-item {
		border-radius: 8px;
		padding: 10px 12px;
		margin-bottom: 8px;
	}
	.main-view.is_pad .cashier-info .cashier-title {
		font-size: 14px;
		margin-bottom: 6px;
	}
	.main-view.is_pad .cashier-info .cashier-title img {
		width: 10px;
		height: 10px;
	}
	.main-view.is_pad .cashier-content .cashier-content-item .menu-button .el-button {
		width: 125px;
	}
	.main-view.is_pad .cashier-info.get-off .cashier-content {
		padding-bottom: 0;
	}
	.main-view.is_pad .hung-list {
		font-size: 13px;
	}
	.main-view.is_pad .hung-list .order-info span {
		display: block;
	}
	.main-view.is_pad .order-detail .el-image {
		width: 78px;
		height: 78px;
	}
	.main-view.is_pad .order-detail .order-detail-goods-name {
		width: 30%;
	}
	.main-view.is_pad .order-detail .el-button {
		height: 24px;
		line-height: 24px!important;
	}
	.main-view.is_pad .refund-detail {
		padding-top: 10px;
	}
	.main-view.is_pad .refund-detail .el-image {
		height: 78px;
		width: 78px;
	}
	.main-view.is_pad .refund-info .refund-label {
		margin: 12px 0 6px;
	}
	.main-view.is_pad .quick-input {
		padding: 1px 4px;
		font-size: 12px;
		margin: 0 4px;
	}
    .vip-price {
        width: 94px;
        height: 17px;
        margin: -5px auto 18px;
    }
    .vip-price>div {
        height: 17px;
        width: 50%;
    }
    .vip-price .vip-left {
        border-top-left-radius: 9px;
        border-bottom-left-radius: 9px;
        background-color: #4e4040;
        position: relative;
    }
    .vip-price .vip-right {
        border-top-right-radius: 9px;
        border-bottom-right-radius: 9px;
        background: linear-gradient(45deg, #edc9a8, #fdebde);
        font-size: 13px;
        line-height: 17px;
        text-align: center;
        color: #4e4040;
    }
    .vip-price .vip-icon {
        width: 33px;
        height: 9px;
        position:absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .main-view .detail-goods-attr .el-button.el-button--info.is-disabled {
    	color: #cdcdcd;
    	background-color: #f7f7f7;
    }
    .el-message.el-message--error {
    	top: 20%!important;
    	margin-left: -7%;
    }
</style>
<div id="app" v-cloak>
	<teller-head v-if="mall" :pad="is_pad" :mall="mall" :setting="setting" :nickname="cashier.nickname" :cashier_info="cashierInfo ? true : false" :is_cashier="is_cashier" @out="loginout" @change="transition" @show="showInfo"></teller-head>
	<div class="main-view" @click="" :class="is_pad ? 'is_pad': ''" :style="{'padding-top': head_height + 'px'}" v-if="!is_cashier" flex="main:justify">
		<!-- 左侧分类 -->
		<div flex-box="0" class="goods-cat" v-if="tabList.length > 0 && setting.is_tab == 1">
			<div style="background-color: #d7efff;">
				<div @click="chooseCatTab(idx)" class="goods-cat-item" :class="idx == 0 && tabIndex == -1 ? 'active' : idx == tabIndex ? 'last-active': ''" v-for="(item,idx) in tabList"><div class="active-line"></div>{{item.label}}</div>
			</div>
		</div>
		<div v-loading="loading" flex-box="1" class="main-center" :class="activeTab == 1 || activeTab == 2 || addMoney || showHung || addCredit || changePrice || loginMember || step > 0 || goods || successPay ? 'padding':''">
			<!-- 顶部tab -->
			<div class="tab" flex="dir:left">
				<div @click="chooseTab(0)" class="tab-item" :class="activeTab == 0 && !stepTab && step != 2 && !loginMember && !addCredit ? 'active right' : activeTab == 1 ? 'last-active': ''">
					<div flex="main:center cross:center">
						<img v-if="activeTab == 0 && step == 0 && !loginMember && !addCredit" src="./../plugins/teller/assets/img/goods-active.png" alt="">
						<img v-else src="./../plugins/teller/assets/img/goods.png" alt="">
						<div :style="{'color': activeTab == 0 && step == 0 && !loginMember && !addCredit ? '#3399ff' : '#adb2b8'}">商品</div>
					</div>
				</div>
				<div @click="chooseTab(1)" class="tab-item" :class="(activeTab == 1 && !stepTab && step == 0) || (step == 2 && !stepTab) || loginMember || addCredit || stepTab == 1 ? 'active left right' : activeTab == 2 ? 'last-active': ''">
					<div flex="main:center cross:center">
						<img v-if="activeTab == 1 || (step == 2 && !stepTab) || loginMember || addCredit" src="./../plugins/teller/assets/img/member-active.png" alt="">
						<img v-else src="./../plugins/teller/assets/img/member.png" alt="">
						<div :style="{'color': (activeTab == 1 && step == 0) || (step == 2 && !stepTab) || loginMember || addCredit ? '#3399ff' : '#adb2b8'}">会员</div>
					</div>
				</div>
				<div @click="chooseTab(2)" class="tab-item" :class="(activeTab == 2 && !stepTab && step != 2) || stepTab == 2 ? 'active left' : ''">
					<div flex="main:center cross:center">
						<img v-if="activeTab == 2 && step == 0" src="./../plugins/teller/assets/img/order-active.png" alt="">
						<img v-else src="./../plugins/teller/assets/img/order.png" alt="">
						<div :style="{'color': activeTab == 2 && step == 0 ? '#3399ff' : '#adb2b8'}">订单</div>
					</div>
				</div>
				<div @click="chooseTab(3)" v-if="fullReduce || activeTab == 3" class="tab-item" :class="activeTab == 3 && !stepTab && step != 2 && !loginMember && !addCredit ? 'active left' : ''">
					<div flex="main:center cross:center">
						<img v-if="activeTab == 3 && step == 0" src="./../plugins/teller/assets/img/full-active.png" alt="">
						<img v-else src="./../plugins/teller/assets/img/full.png" alt="">
						<div :style="{'color': activeTab == 3 && step == 0 ? '#3399ff' : '#adb2b8'}">满减</div>
					</div>
				</div>
			</div>
			<div class="other-title" v-if="step > 1 && !stepTab && is_pad">
				<div flex="main:justify cross:center" v-if="sales.length > 0 && step != 1">
					<div v-if="sale" flex="dir:left cross:centere">
						<div>导购员<span style="margin: 0 22px 0 7px">{{sale.name}}</span></div>
						<div>编号<span style="margin: 0 22px 0 7px">{{sale.number}}</span></div>
					</div>
					<div v-else>未选择导购员</div>
					<el-button :loading="memberLoading || couponLoading" size="small" type="primary" @click="nextSubmit" round>{{sale ? '切换导购员' : '选择导购员'}}</el-button>
				</div>
				<div flex="main:justify cross:center" v-if="step > 2 || (addCredit && !loginMember)">
					<div v-if="member" flex="dir:left cross:center">
						<div flex="dir:left cross:center"><span class="t-omit" style="max-width: 120px;width: auto;">{{member.member_name}}</span><span class="t-omit" style="max-width: 85px;margin: 0 22px 0 7px;width: auto;">{{member.nickname}}</span></div>
						<div>余额<span style="margin: 0 22px 0 7px">{{member.balance}}</span></div>
					</div>
					<div v-else>未选择会员</div>
					<div>
						<el-button :loading="memberLoading || couponLoading" v-if="setting.is_coupon == 1 || setting.is_integral == 1" size="small" v-if="member" type="primary" @click="useDiscount" round>选择优惠抵扣</el-button>
						<el-button :loading="memberLoading || couponLoading" size="small" type="primary" @click="member=null;mobile='';step=2" round>{{member ? '切换会员' : '选择会员'}}</el-button>
					</div>
				</div>
			</div>
			<!-- 付款成功 -->
			<div v-if="successPay" class="choose-goods" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center">
					<div @click="clearOrder" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>收款结果</div>
				</div>
				<div style="height: 100%;background-color: #fff" flex="dir:top main:center cross:center">
					<img src="./../plugins/teller/assets/img/success.png" alt="">
					<div style="font-size: 16px;margin-top: 24px;color: #999999;">收款成功!</div>
					<el-button @click="clearOrder" type="primary" style="width: 234px;margin-top: 100px;" plain round>返回商品页</el-button>
				</div>
			</div>
			<!-- 会员充值 -->
			<div class="choose-goods" v-else-if="addCredit && !loginMember && step < 3 && step != 1" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_other_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center">
					<div @click="rechargeBack" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>{{chooseRecharge ? '选择充值方案' : '会员充值'}}</div>
				</div>
				<div class="choose-goods-info member" :class="chooseRecharge? 'choose-recharge':''" :style="{'padding-left': is_pad || !member || !chooseRecharge ? '0': '28%'}" :flex="chooseRecharge ? 'dir:top':'main:center dir:top'">
					<div v-if="!member" flex="main:center dir:top cross:center" :style="{'margin-top': is_pad ?'15%':0}">
						<img class="member-icon" src="./../plugins/teller/assets/img/unlogin.png" alt="">
						<div style="text-align: center;color: #999999;">请先登录会员</div>
						<div class="member-login" @click="toLoginMember">去登录</div>
					</div>
					<div v-else-if="member && chooseRecharge" flex="dir:top" style="padding-bottom: 100px">
						<div @click="toggleRecharge(item)" class="recharge-item" flex="dir:left corss:center" v-for="item in rechargeList" :class="rechargeId == item.id ? 'active': ''" :key="item.id">
							<div class="recharge-item-name" flex="main:center cross:center">充{{item.pay_price}}元</div>
							<div class="recharge-item-content" flex="dir:left cross:center">
								<span v-if="item.send_type & 0b00000001 && item.send_price">送<span>{{item.send_price}}</span>余额</span>
								<span v-if="item.send_type & 0b00000010">送<span>{{item.send_integral}}</span>积分</span>
								<span v-if="item.send_type & 0b00001000 && item.send_coupon && item.send_coupon.length == 1">
									<span>{{item.send_coupon[0].type == 2 ? item.send_coupon[0].sub_price : item.send_coupon[0].discount }}</span>{{item.send_coupon[0].type == 2 ? '元' : '折'}}{{item.send_coupon[0].name}}{{item.send_coupon[0].num > 1 ? 'x' + item.send_coupon[0].num : ''}}
								</span>
								<span v-if="item.send_type & 0b00001000 && item.send_coupon && item.send_coupon.length > 1">优惠券x{{item.send_coupon_num}}</span>
								<span v-if="item.send_type & 0b00010000 && item.send_card && item.send_card.length == 1">{{item.send_card[0].name}}{{item.send_card[0].num > 1 ? 'x' + item.send_card[0].num : ''}}</span>
								<span v-if="item.send_type & 0b00010000 && item.send_card && item.send_card.length > 1">卡券x{{item.send_card_num}}</span>
								<span v-if="item.send_type & 0b00000100 && item.member && item.member.id > 0">送<span>{{item.member.name}}</span></span>
								<span v-if="item.send_type & 0b00100000 && item.lottery_limit > 0 && is_plugin_show"><span>{{item.lottery_limit}}</span>次抽奖机会</span>
							</div>
						</div>
						<div @click="nextStep" v-if="rechargeList.length > 0" class="next-step absolute">下一步</div>
					</div>
					<div v-else-if="member && inputRecharge" flex="main:center" :style="{'padding-left': is_pad ? '0': '28%'}">
						<app-number-input @status="getInputSataus" :pad="is_pad" label="自定义输入" ref="recharge" :name="member && inputRecharge ? 'recharge' : ''" :width="numberWidth" placeholder="请输入金额" @change="submitRecharge"></app-number-input>
					</div>
					<div flex="main:center dir:top" v-else>
						<img class="member-icon" :style="{'margin-bottom': is_pad ? '0':'20px'}" src="./../plugins/teller/assets/img/unlogin.png" alt="">
						<div style="text-align: center;color: #999999;">当前会员可用余额：<span style="color: #ff4544;font-size: 15px;">￥{{member.balance}}</span></div>
						<div style="margin-top: 22px;" flex="dir:top cross:center main:center">
							<div @click="creditType = 1" class="add-credit-type" :class="creditType == 1 ? 'active' : ''" flex="main:center cross:center">
								<img v-if="creditType == 1" class="active" src="./../plugins/teller/assets/img/active.png" alt="">
								<img src="./../plugins/teller/assets/img/project.png" alt="">
								<div>选择充值方式</div>
							</div>
							<div @click="creditType = 2" class="add-credit-type" :class="creditType == 2 ? 'active' : ''" flex="main:center cross:center">
								<img v-if="creditType == 2" class="active" src="./../plugins/teller/assets/img/active.png" alt="">
								<img src="./../plugins/teller/assets/img/print.png" alt="">
								<div>手动输入金额</div>
							</div>
						</div>
						<div @click="submitCreditType" class="next-step" style="margin: 40px auto;">下一步</div>
					</div>
				</div>
			</div>
			<!-- 挂单列表 -->
			<div v-else-if="showHung" class="choose-goods" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center">
					<div @click="showHung = false;" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>取单</div>
				</div>
				<div class="choose-goods-info">
					<teller-order :stop="!showHung" :list="hungOrder" type="hung" @remark="addRemark" @click="toOrderDetail" @change="delHung" @add="addCount"></teller-order>
				</div>
			</div>
			<!-- 选择加钱 -->
			<div class="choose-goods" v-else-if="addMoney" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center">
					<div @click="addMoney = false;" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>加钱</div>
				</div>
				<div class="choose-goods-info member" flex="main:center dir:top">
					<img class="member-icon no-margin" src="./../plugins/teller/assets/img/goods-pic.png" alt="">
					<div style="text-align: center;color: #999999;">请添加指定金额加入结算清单</div>
					<app-number-input @status="getInputSataus" :pad="is_pad" ref="add" :name="addMoney ? 'add':''" :width="numberWidth" placeholder="请输入金额" @change="addMoneyGoods"></app-number-input>
				</div>
			</div>
			<!-- 选择改价 -->
			<div class="choose-goods" v-else-if="changePrice" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center">
					<div @click="changePrice = false;" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>改价</div>
				</div>
				<div class="choose-goods-info member" flex="main:center dir:top">
					<div class="money-show no-margin">
						<div>应收金额</div>
						<div class="total-money-show">￥<span>{{totalPrice}}</span></div>
					</div>
					<div class="change-type" flex="main:center cross:center">
						<div v-if="(setting.is_goods_change_price_type == 1 && setting.most_plus) || (setting.is_goods_change_price_type == 2 && setting.most_plus_percent)" @click="changeType = 1" :class="changeType == 1 ? 'active' : ''">加价</div>
						<div v-if="(setting.is_goods_change_price_type == 1 && setting.most_subtract) || (setting.is_goods_change_price_type == 2 && setting.most_subtract_percent)" @click="changeType = 2" :class="changeType == 2 ? 'active' : ''">减价</div>
					</div>
					<app-number-input @status="getInputSataus" :pad="is_pad" :price="changeMoney" ref="change" :name="changePrice? 'change':''" :width="numberWidth" :margin="5" placeholder="请输入金额" @change="changeMoneyInput">
						<template>
							<div style="text-align: center;margin-bottom: 5px;color: #cdcdcd">
								<span v-if="setting.most_plus || setting.most_plus_percent">加价范围在{{orderInfo.change_price_data.most_plus_start}}~{{orderInfo.change_price_data.most_plus_end}}内</span>
								<span v-if="(setting.most_plus && setting.most_subtract) || (setting.most_plus_percent && setting.most_subtract_percent)">，</span>
								<span v-if="setting.most_subtract || setting.most_subtract_percent">减价范围在{{orderInfo.change_price_data.most_subtract_start}}~{{orderInfo.change_price_data.most_subtract_end}}内</span>
							</div>
						</template>
					</app-number-input>
				</div>
			</div>
			<!-- 选择订单 -->
			<div class="choose-goods" v-else-if="(activeTab == 2 && !stepTab && step == 0) || stepTab == 2" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center">
					<div @click="goback" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>{{refundDetail ? refundDetail.refund ? '售后详情' : '售后' : orderDetail ? '订单详情' : '订单'}}</div>
				</div>
				<div v-if="!orderDetail && !refundDetail" class="input-item other" :style="{'top': is_pad? '90px' : '140px', 'width': '97%'}">
					<div class="input-area">
						<el-input @focus="inputStatus=true" @blur="inputStatus=false"@keyup.enter.native="searchOrderList" size="small" placeholder="请输入订单号搜索" v-model="orderKeyword" clearable @clear="searchOrderList">
							<el-button slot="append" icon="el-icon-search" @click="searchOrderList"></el-button>
						</el-input>
					</div>
				</div>
				<div v-if="order.length == 0" style="height: 100%;background-color: #fff;border-top-left-radius: 16px;" flex="dir:top main:center cross:center">
					<img src="./../plugins/teller/assets/img/no-order.png" alt="">
					<div style="font-size: 16px;margin-top: 24px;color: #999999;">暂无订单</div>
				</div>
				<div v-else-if="refundDetail" class="choose-goods-info">
					<div class="refund-detail" flex="dir:left cross:center">
						<el-image fit="cover" lazy :src="refundDetail.cover_pic" alt=""></el-image>
						<div class="hung-goods-info">
							<div class="order-detail-goods-name">
								<div class="t-omit" style="display: block;">{{refundDetail.name}}</div>
								<div class="t-omit" style="display: block;color: #a1a1a1;margin: 8px 0;">
									<span v-for="(attr,i) in refundDetail.attr" :key="i">{{attr}}</span>
								</div>
							</div>
							<div>x{{refundDetail.num}}</div>
							<div style="color: #ff4544;margin-top: 5px">￥{{refundDetail.total_price}}</div>
						</div>
					</div>
					<div class="refund-info" v-if="refundDetail.refund">
						<div class="refund-label">
							<span>售后类型</span>
							<span style="font-size:15px;margin-left: 15px;color: #353535">{{refundDetail.refund.refund_type}}</span>
						</div>
						<div class="refund-label">
							<span>退款途径</span>
							<span style="font-size:15px;margin-left: 15px;color: #353535">{{orderDetail.pay_type}}</span>
						</div>
						<div class="refund-label">
							<span>退款说明</span>
							<span style="font-size:15px;margin-left: 15px;color: #353535">{{refundDetail.refund.remark ? refundDetail.refund.remark : '无'}}</span>
						</div>
						<div class="refund-label">
							<span>退款金额</span>
							<span style="font-size:15px;margin-left: 15px;color: #ff4544">￥{{refundDetail.refund.refund_price}}</span>
						</div>
						<div class="refund-label">
							<span>退款状态</span>
							<span style="font-size:15px;margin-left: 15px;color: #353535">{{refundDetail.refund.status_text}}</span>
						</div>
					</div>
					<div class="refund-info" v-else>
						<div class="refund-label">售后类型</div>
						<div>
							<el-button @click="refundType = 3" size="small" :type="refundType == 3 ? 'primary' : ''" round>仅退款</el-button>
							<el-button @click="refundType = 1" size="small" :type="refundType == 1 ? 'primary' : ''" round>退货退款</el-button>
						</div>
						<div class="refund-label">退款途径</div>
						<div>
							<el-button size="small" type="primary" round>{{orderDetail.pay_type}}</el-button>
							<span style="margin-left: 12px;color: #999999">默认跟订单下单支付方式一致，若是现金或者pos机，线下自行操作退款</span>
						</div>
						<div class="refund-label">退款说明</div>
						<div style="width: 40%;">
							<el-input @focus="inputStatus=true" @blur="inputStatus=false"type="textarea" :rows="5" maxlength="200" placeholder="请输入文字" v-model="refundRemark"></el-input>
						</div>
						<div class="refund-label">退款金额</div>
						<div>
							<el-input @focus="inputStatus=true" @blur="inputStatus=false"class="refund-price-input" type="number" min="0" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" v-model="refundPrice" :placeholder="'请输入0~' + refundDetail.total_price + '之间的金额'"></el-input>
							<el-button size="small" :loading="refundLoading" @click="toRefund" type="primary" round>确认退款</el-button>
						</div> 
					</div>
				</div>
				<div v-else-if="orderDetail" class="choose-goods-info">
					<div class="hung-list">
						<div class="order-info" flex="dir:left cross:center">
							<div>订单号：<span>{{orderDetail.order_no}}</span></div>
							<div>支付方式：<span>{{orderDetail.pay_type}}</span></div>
							<div v-if="orderDetail.sales_name">导购员：<span>{{orderDetail.sales_name}}</span></div>
							<div>收银员：<span>{{orderDetail.cashier_name}}</span></div>
						</div>
						<div class="order-detail" flex="dir:left cross:center" v-for="(item,index) in orderDetail.detail" :key="item.id">
							<el-image fit="cover" lazy :src="item.cover_pic" alt=""></el-image>
							<div class="order-detail-goods-name">
								<div class="t-omit" style="display: block;">{{item.name}}</div>
								<div class="t-omit" style="display: block;color: #a1a1a1;margin-top: 16px;">
									<span v-for="(attr,i) in item.attr" :key="i">{{attr}}</span>
								</div>
							</div>
							<div>数量：{{item.num}}</div>
							<div>小计：￥{{item.total_price}}</div>
							<div flex="main:center">
								<el-button v-if="item.refund" size="small" @click="refundDetail = item;" round>售后详情</el-button>
								<el-button :disabled="item.is_show_sale == 0" v-else size="small" @click="refundDetail = item;refundType=3;refundRemark='';refundPrice=item.total_price" round>售后</el-button>
							</div>
						</div>
						<div style="margin-bottom: 4px;">订单总价 <span style="color: #ff4544;">￥{{orderDetail.total_pay_price}}</span><span style="margin-left: 16px;color: #999999">等{{orderDetail.goods_count}}件商品</span></div>
						<div style="width: 60%;">
							<el-tag v-if="orderDetail.seller_remark" type="warning" size="small" style="border:0;margin: 5px 0">{{ orderDetail.seller_remark }}</el-tag>
						</div>
					</div>
				</div>
				<div v-else class="choose-goods-info order-list">
					<teller-order :list="order" @load="getOrderList(orderPage)" @remark="addRemark" @click="toOrderDetail"></teller-order>
				</div>
			</div>
			<!-- 选择会员 -->
			<div v-loading="loading || memberLoading || couponLoading" class="choose-goods" v-else-if="(activeTab == 1 && !stepTab && step == 0) || step == 2 || loginMember || stepTab == 1" :style="{'padding-top': is_pad ? step > 1 && !stepTab ? '8px' : '50px' :'90px','padding-bottom': view_other_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center" :class="step > 1 && !stepTab && is_pad && sales.length > 0 ? 'have-sale':''">
					<div @click="memberBack" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>选择会员</div>
				</div>
				<div class="choose-goods-info member" :class="sales.length > 0 ? '' :'attr-view'" v-if="!member" flex="main:center dir:top">
					<img class="member-icon" src="./../plugins/teller/assets/img/pay.png" alt="">
					<div style="text-align: center;color: #999999;">请扫描付款码或者输入会员手机号以便查询会员</div>
					<app-number-input @status="getInputSataus" :pad="is_pad" ref="member" :name="!member ? 'member': ''" mode="number" :width="numberWidth" placeholder="请扫描付款码或会员手机号" @change="submitMember"></app-number-input>
				</div>
				<div class="choose-goods-info member" v-else>
					<div class="member-info" v-loading="memberLoading">
						<img class="member-avatar" :src="member.avatar" alt="">
						<div style="text-align: center;margin-bottom: 15px;">{{member.nickname}}</div>
					    <div v-if="member.vip_discount || member.vip_discount == '0.0'" class="vip-price" flex="dir:left">
					        <div class="vip-left">
					            <img class="vip-icon" src="./../plugins/teller/assets/img/S-VIP.png">
					        </div>
					        <div class="vip-right">{{member.vip_discount == 0 ? '免费' :  member.vip_discount +'折' }}</div>
					    </div>
						<div style="margin-bottom: 10px;"><span class="member-label">会员类型</span>{{member.member_name}}</div>
						<div style="margin-bottom: 10px;"><span class="member-label">可用积分</span>{{addCredit ? member.integral : orderInfo ? orderInfo.integral.use_num : member.integral}}</div>
						<div style="margin-bottom: 10px;"><span class="member-label">可用余额</span>￥{{member.balance}}</div>
						<div style="margin-bottom: 10px;"><span class="member-label">可用优惠券</span>{{addCredit ? member.coupon_count : couponList.length}}张</div>
						<div v-if="setting.is_coupon == 1 || setting.is_integral == 1">
							<el-button type="primary" @click="useDiscount" plain round>
								<div flex="main:center cross:center">
									<img src="./../plugins/teller/assets/img/use.png" alt="">
									<span style="margin-left: 4px">使用优惠抵扣</span>
								</div>
							</el-button>
						</div>
						<div>
							<el-button @click="reSearch" round>重新搜索</el-button>
						</div>
						<div>
							<el-button type="primary" @click="checkInMember" round>确定</el-button>
						</div>
					</div>
				</div>
			</div>
			<!-- 选择规格 -->
			<div v-else-if="goods" class="choose-goods" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center">
					<div @click="goods = null" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>选择商品</div>
				</div>
				<div class="choose-goods-info attr-view">
					<div class="detail-goods" flex="dir:left cross:center">
						<el-image fit="cover" lazy :src="selectAttr && selectAttr.pic_url ? selectAttr.pic_url : goods.cover_pic" alt=""></el-image>
						<div class="detail-goods-info">
							<div class="detail-goods-name">{{goods.name}}</div>
							<div v-if="!selectAttr" style="color: #ff4544;font-size: 16px;">￥{{goods.price_min}}<span v-if="goods.price_max > goods.price_min">~￥{{goods.price_max}}</span><span style="margin-left: 16px;font-size: 15px;color: #999999">库存{{goods.goods_stock}}</span></div>
							<div v-else style="color: #ff4544;font-size: 16px;">￥{{selectAttr.price}}</span><span style="margin-left: 16px;font-size: 15px;color: #999999">库存{{selectAttr.stock}}</span></div>
						</div>
					</div>
					<div>
						<div class="detail-goods-attr" v-for="(attr_groups,idx) in goods.attr_groups" :key="attr_groups.attr_group_id">
							<div class="detail-attr-name">{{attr_groups.attr_group_name}}</div>
							<div>
								<el-button @click="chooseAttr(attr,idx,index)" size="small" :disabled="idx == goods.attr_groups.length - 1 && attr.stock == 0" :type="attr.active ? 'primary':'info'" round v-for="(attr,index) in attr_groups.attr_list" :key="attr.attr_id">{{attr.attr_name}}</el-button>
							</div>
						</div>
					</div>
					<div class="detail-goods-attr detail-goods-number" v-if="selectAttr.stock > 0" :style="{'bottom': view_bottom + 'px'}">
						<div class="detail-attr-name">数量</div>
						<div flex="dir:left cross:center">
							<img style="cursor: pointer" @click="handleGoods('low')" src="./../plugins/teller/assets/img/low.png" alt="">
							<el-input @focus="inputStatus=true" @blur="inputStatus=false"size="small" type="number" oninput="this.value = this.value.replace(/[^0-9]/g, '');" min="0" :max="selectAttr.stock" v-model="number"></el-input>
							<img style="cursor: pointer;margin-right: 15px" @click="handleGoods('add')" src="statics/img/plugins/teller-add.png" alt="">
							<el-button size="small" @click="submitGoods" type="primary" round>确定</el-button>
						</div>
					</div>
				</div>
			</div>
			<!-- 选择导购员 -->
			<div class="choose-goods" v-else-if="step == 1 && sales.length > 0" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center">
					<div @click="goback" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>选择导购员</div>
				</div>
				<div class="choose-goods-info" flex="dir:left" style="flex-wrap: wrap;align-content:flex-start">
					<div @click="saleIndex=-1;sale=null" class="sales-item" :class="saleIndex == -1 ? 'active': ''"flex="main:center cross:center">
						<div style="font-size: 20px">
							无
						</div>
					</div>
					<div @click="showSales(item,index)" class="sales-item" :class="saleIndex == index ? 'active': ''" v-for="(item,index) in sales" :key="index" flex="dir:left cross:center">
						<img :src="item.head_url? item.head_url : 'statics/img/app/user-default-avatar.png'" alt="">
						<div>
							<div>{{item.number}}</div>
							<div style="margin-top: 5px;">{{item.name}}</div>
						</div>
					</div>
				</div>
			</div>
			<!-- 现金收款 -->
			<div class="choose-goods" v-else-if="step == 4 && payment_type == 'cash'" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_other_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center"  :class="is_pad && (sales.length == 0 && step > 2) ? 'have-sale':''">
					<div @click="goback" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>现金收款</div>
				</div>
				<div class="choose-goods-info member" flex="dir:top main:center">
					<div class="money-show">
						<div>应收金额</div>
						<div class="total-money-show">￥<span>{{addCredit ? payPrice :totalPrice}}</span></div>
					</div>
					<app-number-input @status="getInputSataus" :pad="is_pad" :price="getMoney" ref="cash" :name="step == 4 && payment_type == 'cash' ? 'cash':''" :width="numberWidth" :margin="5" placeholder="实收金额" @change="submitPay" @input="getMoneyInput">
						<template>
							<div flex="main:justify" :style="{'width': numberWidth / 3.5 > 480 ? '480px':numberWidth / 3.5 +'px', 'margin': is_pad ? '6px auto 10px' : '20px auto','font-size': is_pad? '14':'16px','color': '666666'}">
								<div flex="dir:left cross:center">
									<div>快捷输入</div>
									<div @click="getMoney = 300" class="quick-input">￥300</div>
									<div @click="getMoney = 600" class="quick-input">￥600</div>
									<div @click="getMoney = 50" class="quick-input">￥50</div>
								</div>
								<div v-if="addCredit">找零<span style="color: #ff4544;">￥{{+getMoney > +payPrice ? (getMoney - +payPrice).toFixed(2): '0.00'}}</span></div>
								<div v-else>找零<span style="color: #ff4544;">￥{{+getMoney > +totalPrice ? (getMoney - +totalPrice).toFixed(2): '0.00'}}</span></div>
							</div>
						</template>
					</app-number-input>
				</div>
			</div>
			<!-- 会员余额收款 -->
			<div class="choose-goods" v-else-if="step > 3 && payment_type == 'balance'" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_other_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center"  :class="is_pad && (sales.length == 0 && step > 2) ? 'have-sale':''" flex="dir:left cross:center">
					<div @click="goback" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>{{verifyPassword ?  '确认支付密码' : setPassword || (step == 5 && balanceType == 2) ? '输入支付密码': '会员余额收款'}}</div>
				</div>
				<div class="choose-goods-info member" flex="dir:top main:center">
					<div class="money-show" v-if="!(step == 5 && balanceType == 2) && !setPassword">
						<div>应收金额</div>
						<div class="total-money-show">￥<span>{{totalPrice}}</span></div>
					</div>
					<img class="money-show-img" v-if="step == 5 && balanceType == 1" src="./../plugins/teller/assets/img/pay-qr.png" alt="">
					<div flex="main:center dir:top" v-if="step == 5 && balanceType == 1">
						<div style="text-align: center;color: #999999;">请使用扫码枪扫描客户付款码</div>
	            		<el-input @focus="inputStatus=true" @blur="inputStatus=false" @keyup.enter.native="submitPay" ref="barcode" autofocus size="small" class="barcode-input" v-model="memberBarcode" placeholder="请输入付款码"></el-input>
						<div :style="{'margin-top': is_pad?'26px':'40px'}" flex="main:center" v-loading="countLoading">
							<div  @click="submitPay" class="next-step">确认收款</div>
						</div>
					</div>
					<div flex="main:center dir:top" v-if="(step == 5 && balanceType == 2) || setPassword">
						<app-number-input @status="getInputSataus" :pad="is_pad" :name="setPassword ? verifyPassword ? 'verify' : 'set' : passwordInputName" mode="password" :width="numberWidth" @change="checkPayPassword"></app-number-input>
					</div>
					<div v-loading="memberLoading" class="balance-pay-type" flex="main:center dir:top" v-if="step == 4 && !setPassword">
						<img class="member-icon" :style="{'margin-bottom': is_pad ? '0':'20px','margin-top': is_pad ? '0': '5px'}" src="./../plugins/teller/assets/img/unlogin.png" alt="">
						<div style="text-align: center;color: #999999;">当前会员可用余额：<span style="color: #ff4544;font-size: 15px;">￥{{member.balance}}</span></div>
						<div :style="{'margin-top': is_pad ? '12px': '22px'}" flex="dir:top cross:center main:center">
							<div @click="balanceType = 1" class="add-credit-type" :class="balanceType == 1 ? 'active' : ''" flex="main:center cross:center">
								<img v-if="balanceType == 1" class="active" src="./../plugins/teller/assets/img/active.png" alt="">
								<img src="./../plugins/teller/assets/img/code.png" alt="">
								<div>动态付款码</div>
							</div>
							<div v-if="setting.is_balance_pay_password == 1" @click="balanceType = 2" class="add-credit-type" :class="balanceType == 2 ? 'active' : ''" flex="main:center cross:center">
								<img v-if="balanceType == 2" class="active" src="./../plugins/teller/assets/img/active.png" alt="">
								<img src="./../plugins/teller/assets/img/password.png" alt="">
								<div>余额支付密码</div>
							</div>
						</div>
						<div @click="submitBalanceType" class="next-step absolute">下一步</div>
					</div>
				</div>
			</div>
			<!-- 其他收款 -->
			<div class="choose-goods" v-else-if="step == 4 && payment_type != 'cash' && payment_type != 'balance'" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_other_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center"  :class="is_pad && (sales.length == 0 && step > 2) ? 'have-sale':''">
					<div @click="goback" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>{{payment_type == 'pos' ? 'POS机收款':payment_type == 'wechat_scan' ? '微信收款':'支付宝收款'}}</div>
				</div>
				<div class="choose-goods-info member" flex="dir:top main:center">
					<div class="money-show">
						<div>应收金额</div>
						<div class="total-money-show">￥<span>{{addCredit ? payPrice :totalPrice}}</span></div>
					</div>
					<img class="money-show-img" v-if="payment_type == 'pos'" style="width: 114px;" src="./../plugins/teller/assets/img/pay-pos.png" alt="">
					<img v-else class="money-show-img" src="./../plugins/teller/assets/img/pay-qr.png" alt="">
					<div v-if="payment_type == 'pos'" style="text-align: center;color: #999999;">请引导顾客刷卡支付</div>
					<div v-else style="text-align: center;color: #999999;">请使用扫码枪扫描客户付款码</div>
            		<el-input @focus="inputStatus=true" @blur="inputStatus=false"@keyup.enter.native="submitPay" ref="barcode" v-if="payment_type != 'pos'" autofocus size="small" class="barcode-input" v-model="barcode" placeholder="请输入付款码"></el-input>
					<div :style="{'margin-top': is_pad?'26px':'40px'}" flex="main:center" v-loading="countLoading">
						<div  @click="submitPay" class="next-step">确认收款</div>
					</div>
				</div>
			</div>
			<!-- 选择支付方式 -->
			<div class="choose-goods" v-else-if="activeTab == 1 || step == 3" :style="{'padding-top': is_pad ? '50px':'90px','padding-bottom': view_other_bottom + 'px'}">
				<div class="choose-goods-title" flex="dir:left cross:center"  :class="is_pad && (sales.length == 0 && step > 2) ? 'have-sale':''">
					<div @click="goback" flex="main:center cross:center" class="return-btn">
						<img width="19px" height="16px" style="margin-right: 10px;" src="./../plugins/teller/assets/img/return.png" alt="">
						<div>返回</div>
					</div>
					<div>选择支付方式</div>
				</div>
				<div class="choose-goods-info" flex="main:center dir:top">
					<div class="pay-list" :style="{'justify-content': setting.payment_type.length > 3 ? 'flex-start' : 'center'}">
						<div @click="payment_type = item" :class="payment_type == item ? 'active':''" class="pay-type" v-for="(item,index) in setting.payment_type" :key="index" flex="dir:top main:center cross:center" v-if="(addCredit && item != 'balance') || (!addCredit && ((member && item == 'balance') || item != 'balance'))">
							<img v-if="payment_type == item" :src="'./../plugins/teller/assets/img/' + item + '_active.png'" alt="">
							<img v-else :src="'./../plugins/teller/assets/img/' + item + '.png'" alt="">
							<div>
								{{item == 'wechat_scan' ? '微信' : item == 'alipay_scan' ? '支付宝' : item == 'balance' ? '余额' : item == 'cash' ? '现金' : 'POS'}}
							</div>
						</div>
					</div>

					<div class="pay-list" v-loading="countLoading" style="justify-content: center;margin-top: 0;">
						<div @click="nextStep" class="next-step">确定</div>
					</div>
				</div>
			</div>
			<!-- 选择满减商品 -->
			<div class="goods-view" :style="{'padding-bottom': (screenHeight - 70) * 0.0859 + 'px'}" v-else-if="(activeTab == 3 && step == 0) || stepTab == 3">
				<div class="input-item">
					<div class="full-reduce">满减优惠：
							<template v-if="fullReduce.rule_type === 1">
								<span v-for="(item, index) in fullReduce.rule"
									   :key="index">
									满{{item.min_money}}{{item.discount_type === '1' ? '减' + item.cut : '打' + item.discount + '折'}}{{index !== fullReduce.rule.length - 1 ? ', ' : ''}}
								</span>
							</template>
							<template v-else-if="fullReduce.rule_type === 2">
								每满{{fullReduce.rule.min_money}}减{{fullReduce.rule.cut}}
							</template>
					</div>
					<div class="input-area">
						<el-input @focus="inputStatus=true" @blur="inputStatus=false"@keyup.enter.native="toSearch" size="small" placeholder="请输入商品名称/条码" v-model="fullKeyword" clearable @clear="toSearch">
							<el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
						</el-input>
					</div>
				</div>
				<teller-goods name="full" :pad="is_pad" :list="fullList" @load="getFullList(fullGoodsPage)" @click="toDetail" :top="131"></teller-goods>
			</div>
			<!-- 选择商品 -->
			<div class="goods-view" :style="{'padding-bottom': (screenHeight - 70) * 0.0859 + 'px'}" v-else-if="activeTab == 0 && step == 0">
				<div class="input-item">
					<div class="input-area">
						<el-input @focus="inputStatus=true" @blur="inputStatus=false"@keyup.enter.native="toSearch" size="small" placeholder="请输入商品名称/条码" v-model="keyword" clearable @clear="toSearch">
							<el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
						</el-input>
					</div>
				</div>
				<teller-goods name="goods" :pad="is_pad" @load="getList(goodsPage)" :list="list" @click="toDetail"></teller-goods>
			</div>
			<teller-bottom :setting="setting" :length="hungLength" :add="addMoney" :show="showHung" :change="changePrice" :credit="addCredit" @to="toHung" @get="getOrder" @click="toggleView"></teller-bottom>
			<div class="other-info" v-if="!is_pad && !stepTab && ((activeTab != 0 && activeTab != 3) || step > 1)">
				<div class="other-item" flex="dir:top cross:center" v-if="(step > 1 || chooseRecharge || inputRecharge) && sales.length > 0 && !successPay">
					<div class="other-item-line" v-if="sale" flex="main:justify">
						<div class="other-item-label">导购员</div>
						<div>{{sale.name}}</div>
					</div>
					<div class="other-item-line" v-if="sale" flex="main:justify">
						<div class="other-item-label">编号</div>
						<div>{{sale.number}}</div>
					</div>
					<div class="other-item-line" :style="{'margin-bottom': !sale ? '0': '5px'}" flex="main:justify cross:center">
						<div :style="{'visibility': !sale ? 'visible' : 'hidden'}">未选择导购员</div>
						<el-button size="small" type="primary" @click="nextSubmit" round>{{sale ? '切换导购员' : '选择导购员'}}</el-button>
					</div>
				</div>
				<div class="other-item" flex="dir:top cross:center" v-if="(step > 2 && !successPay) || (addCredit && member && !loginMember && step != 1)">
					<div class="other-item-line" v-if="member" flex="main:justify">
						<div class="other-item-label member-name">{{member.member_name}}</div>
						<div>{{member.nickname}}</div>
					</div>
					<div class="other-item-line" v-if="member" flex="main:justify">
						<div class="other-item-label">余额</div>
						<div>{{member.balance}}</div>
					</div>
					<div class="other-item-line" :style="{'margin-bottom': !member ? '0': '5px'}" flex="main:justify cross:center">
						<el-button size="small" v-if="member && (setting.is_coupon == 1 || setting.is_integral == 1)" type="primary" @click="useDiscount" round>选择优惠抵扣</el-button>
						<div v-if="!member">未选择会员</div>
						<el-button size="small" type="primary" @click="member=null;mobile='';step=2" round>{{member ? '切换会员' : '选择会员'}}</el-button>
					</div>
				</div>
			</div>
			<div v-if="(step == 1 || step == 2) && !(addCredit && step != 1) && !stepTab && !changePrice && !addMoney && !(((activeTab == 1 && step == 0) || step == 2 || loginMember) && member)" @click="nextStep" class="next-step absolute">下一步</div>
		</div>
		<div flex-box="0" class="menu">
			<div class="menu-list">
				<div class="menu-title">
					<div>
						结算清单（{{totalCountNumber}}件）
						<el-tag style="margin-left: 18px;" effect="plain" size="mini">收银员：{{cashier.number}}</el-tag>
					</div>
				</div>
				<div class="menu-goods-list">
					<div style="margin-top: 30%;" flex="dir:top main:center cross:center" v-if="count.length == 0">
						<img src="./../plugins/teller/assets/img/no-choose.png">
						<div style="margin-top: 25px;color: #999">还没有选中任何商品</div>
					</div>
					<div class="menu-goods-item" flex="dir:left" v-for="(item,index) in count" :key="item.attr_id">
						<el-image fit="cover" lazy :src="item.cover_pic" alt=""></el-image>
						<div class="menu-goods-info dir-top-nowrap cross-center">
							<div class="menu-goods-name">{{item.name}}</div>
							<div :style="{'visibility': temp && item.id == temp.id ? 'hidden' : 'visible'}" style="width: 50%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
								<span style="margin-right: 5px" v-for="(attr,idx) in item.attr" :key="idx">{{attr.attr_group_name}}:{{attr.attr_name}}</span>
							</div>
							<div class="menu-goods-price">{{item.selectAttr ? item.selectAttr.price : item.price}}</div>
							<div class="menu-goods-number" flex="dir:left cross:center">
								<img @click="handleMenu(index,'del')" src="./../plugins/teller/assets/img/delete.png" alt="">
								<img @click="handleMenu(index,'low')" src="./../plugins/teller/assets/img/low.png" alt="">
								<div>{{item.num}}</div>
								<img @click="handleMenu(index,'add')" src="statics/img/plugins/teller-add.png" alt="">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="menu-bottom" :class="is_pad?'pad-menu-bottom':''">
				<div class="discount" flex="main:justify">
					<div>本次优惠
						<el-button v-show="orderInfo.deduction_price > 0 || (orderInfo.coupon && orderInfo.coupon.coupon_discount > 0) || orderInfo.full_reduce_discount > 0 || orderInfo.member_discount > 0 || orderInfo.vip_discount > 0 || +orderInfo.total_change_price > 0 || orderInfo.erase_price > 0 || orderInfo.erase_price < 0 || (orderInfo.integral && orderInfo.integral.use && orderInfo.integral.deduction_price > 0)" @click="discountVisible = true;" size="mini" type="primary" round>明细</el-button>
					</div>
					<div class="discount-price">{{salePrice}}</div>
				</div>
				<div class="price" flex="main:justify">
					<div>应收金额</div>
					<div style="color: #ff4544;font-weight: 600;font-size: 22px"><span class="discount-price">￥</span>{{totalPrice}}
					</div>
				</div>
				<div class="menu-button" flex="main:justify"> 
					<el-button @click="cancel" round>整单取消</el-button>
					<el-button @click="nextSubmit('count')" class="money" :loading="countLoading" round>收款</el-button>
				</div>
			</div>
		</div>
	</div>
	<div class="main-view other" :style="{'padding-top': is_pad ? screenHeight * 0.0573 + 'px' : '90px'}" :class="is_pad ? 'is_pad': ''" v-if="is_cashier">
		<div class="tab-nav" flex="dir:left">
			<div @click="is_cashier = false" class="tab-nav-item tab-cashier" flex="main:center cross:center">
				<img src="./../plugins/teller/assets/img/cashier.png" alt="">
				<div>收银台</div>
			</div>
			<div class="tab-nav-item" flex="main:center cross:center">个人信息</div>
		</div>
		<div class="cashier-info">
			<img class="cashier-avatar" :src="cashier.avatar? cashier.avatar : 'statics/img/app/user-default-avatar.png'" alt="">
			<el-form class="cashier-about" :model="cashier" label-position="left" label-width="100px">
				<el-form-item label="收银员编号" prop="number">
					<div>{{cashier.number}}</div>
				</el-form-item>
				<el-form-item label="姓名" prop="nickname">
					<div>{{cashier.nickname}}</div>
				</el-form-item>
				<el-form-item label="电话" prop="mobile">
					<div>{{cashier.mobile}}</div>
				</el-form-item>
				<el-form-item label="帐号" prop="username">
					<div>{{cashier.username}}</div>
				</el-form-item>
				<el-form-item label="密码" prop="password">
					<el-button size="small" @click="togglePassword" round>修改密码</el-button>
				</el-form-item>
				<el-form-item label="门店" prop="store_name">
					<div style="width: 400px;">{{cashier.store_name}}</div>
				</el-form-item>
			</el-form>
		</div>
	</div>
	<div class="main-view other" :class="is_pad ? 'is_pad': ''" :style="{'padding-top': is_pad ? screenHeight * 0.0573 + 'px' : '90px'}" v-if="cashierInfo">
		<div class="tab-nav" flex="dir:left">
			<div @click="cashierInfo = null;workDetail = null" class="tab-nav-item tab-cashier" flex="main:center cross:center">
				<img src="./../plugins/teller/assets/img/cashier.png" alt="">
				<div>收银台</div>
			</div>
			<div class="tab-nav-item" flex="main:center cross:center">交班</div>
		</div>
		<div class="cashier-info get-off" :style="{'padding-top': is_pad? '35px':'70px'}">
			<div class="cashier-about">
				<div class="cashier-title" flex="dir:left cross:center">
					<img src="./../plugins/teller/assets/img/cashier-info.png" alt="">
					<div>收银员</div>
				</div>
				<div class="cashier-form">
					<div>收银员：<span>{{cashierInfo.name}}</span></div>
					<div>收银编号：<span>{{cashierInfo.number}}</span></div>
					<div>上班时间：<span>{{cashierInfo.start_time}}</span></div>
					<div>交班时间：<span>{{cashierInfo.end_time}}</span></div>
					<div>时长：<span>{{cashierInfo.hour}}</span></div>
				</div>
			</div>
			<div class="cashier-content">
				<div class="cashier-content-item">
					<div class="cashier-title" flex="dir:left cross:center">
						<img src="./../plugins/teller/assets/img/receiving.png" alt="">
						<div>收款总额</div>
					</div>
					<div flex="dir:left cross:center">
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="cashier-info-label" :style="{'margin-bottom': is_pad?'20px':'40px'}">收款总额</div>
							<div>￥{{workDetail.proceeds.total_proceeds}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #e6f8e8"></div>
							<img src="./../plugins/teller/assets/img/wechat_scan.png" alt="">
							<div class="cashier-info-label">微信</div>
							<div>￥{{workDetail.proceeds.wechat_proceeds}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #e4f1fd"></div>
							<img src="./../plugins/teller/assets/img/alipay_scan.png" alt="">
							<div class="cashier-info-label">支付宝</div>
							<div>￥{{workDetail.proceeds.alipay_proceeds}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #ffe5e5"></div>
							<img src="./../plugins/teller/assets/img/cash.png" alt="">
							<div class="cashier-info-label">现金</div>
							<div>￥{{workDetail.proceeds.cash_proceeds}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #fff1e1"></div>
							<img src="./../plugins/teller/assets/img/balance.png" alt="">
							<div class="cashier-info-label">会员余额</div>
							<div>￥{{workDetail.proceeds.balance_proceeds}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #eae7fb"></div>
							<img src="./../plugins/teller/assets/img/pos.png" alt="">
							<div class="cashier-info-label">POS机</div>
							<div>￥{{workDetail.proceeds.pos_proceeds}}</div>
						</div>
					</div>
				</div>
				<div class="cashier-content-item">
					<div class="cashier-title" flex="dir:left cross:center">
						<img src="./../plugins/teller/assets/img/invest.png" alt="">
						<div>充值总额</div>
					</div>
					<div flex="dir:left cross:center">
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="cashier-info-label" :style="{'margin-bottom': is_pad?'20px':'40px'}">充值总额</div>
							<div>￥{{workDetail.recharge.total_recharge}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #e6f8e8"></div>
							<img src="./../plugins/teller/assets/img/wechat_scan.png" alt="">
							<div class="cashier-info-label">微信</div>
							<div>￥{{workDetail.recharge.wechat_recharge}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #e4f1fd"></div>
							<img src="./../plugins/teller/assets/img/alipay_scan.png" alt="">
							<div class="cashier-info-label">支付宝</div>
							<div>￥{{workDetail.recharge.alipay_recharge}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #ffe5e5"></div>
							<img src="./../plugins/teller/assets/img/cash.png" alt="">
							<div class="cashier-info-label">现金</div>
							<div>￥{{workDetail.recharge.cash_recharge}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #eae7fb"></div>
							<img src="./../plugins/teller/assets/img/pos.png" alt="">
							<div class="cashier-info-label">POS机</div>
							<div>￥{{workDetail.recharge.pos_recharge}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center" style="visibility: hidden;">
							<div class="more" style="background-color: #fff1e1"></div>
							<img src="./../plugins/teller/assets/img/balance.png" alt="">
							<div class="cashier-info-label">会员余额</div>
						</div>
					</div>
				</div>
				<div class="cashier-content-item">
					<div class="cashier-title" flex="dir:left cross:center">
						<img src="./../plugins/teller/assets/img/refund.png" alt="">
						<div>退款总额</div>
					</div>
					<div flex="dir:left cross:center">
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="cashier-info-label" :style="{'margin-bottom': is_pad?'20px':'40px'}">退款总额</div>
							<div>￥{{workDetail.refund.total_refund}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #e6f8e8"></div>
							<img src="./../plugins/teller/assets/img/wechat_scan.png" alt="">
							<div class="cashier-info-label">微信</div>
							<div>￥{{workDetail.refund.wechat_refund}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #e4f1fd"></div>
							<img src="./../plugins/teller/assets/img/alipay_scan.png" alt="">
							<div class="cashier-info-label">支付宝</div>
							<div>￥{{workDetail.refund.alipay_refund}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #ffe5e5"></div>
							<img src="./../plugins/teller/assets/img/cash.png" alt="">
							<div class="cashier-info-label">现金</div>
							<div>￥{{workDetail.refund.cash_refund}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #fff1e1"></div>
							<img src="./../plugins/teller/assets/img/balance.png" alt="">
							<div class="cashier-info-label">会员余额</div>
							<div>￥{{workDetail.refund.balance_refund}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #eae7fb"></div>
							<img src="./../plugins/teller/assets/img/pos.png" alt="">
							<div class="cashier-info-label">POS机</div>
							<div>￥{{workDetail.refund.pos_refund}}</div>
						</div>
					</div>
				</div>
				<div class="cashier-content-item">
					<div class="cashier-title" flex="dir:left cross:center">
						<img src="./../plugins/teller/assets/img/total.png" alt="">
						<div>总计</div>
					</div>
					<div flex="dir:left cross:center">
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="cashier-info-label" :style="{'margin-bottom': is_pad?'20px':'40px'}">收款总额</div>
							<div>￥{{workDetail.aggregate.total_aggregate}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #e6f8e8"></div>
							<img src="./../plugins/teller/assets/img/wechat_scan.png" alt="">
							<div class="cashier-info-label">微信</div>
							<div>￥{{workDetail.aggregate.wechat_aggregate}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #e4f1fd"></div>
							<img src="./../plugins/teller/assets/img/alipay_scan.png" alt="">
							<div class="cashier-info-label">支付宝</div>
							<div>￥{{workDetail.aggregate.alipay_aggregate}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #ffe5e5"></div>
							<img src="./../plugins/teller/assets/img/cash.png" alt="">
							<div class="cashier-info-label">现金</div>
							<div>￥{{workDetail.aggregate.cash_aggregate}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #fff1e1"></div>
							<img src="./../plugins/teller/assets/img/balance.png" alt="">
							<div class="cashier-info-label">会员余额</div>
							<div>￥{{workDetail.aggregate.balance_aggregate}}</div>
						</div>
						<div class="cashier-info-item" flex="dir:top main:center cross:center">
							<div class="more" style="background-color: #eae7fb"></div>
							<img src="./../plugins/teller/assets/img/pos.png" alt="">
							<div class="cashier-info-label">POS机</div>
							<div>￥{{workDetail.aggregate.pos_aggregate}}</div>
						</div>
					</div>
					<div class="menu-button" v-if="is_pad" flex="dir:right"> 
						<el-button :loading="loading" @click="getOff" class="money" round>确认交班</el-button>
						<el-button :loading="loading" @click="print" round>打印小票</el-button>
					</div>
				</div>
				<div class="menu-button" v-if="!is_pad" flex="dir:right"> 
					<el-button :loading="loading" @click="getOff" class="money" round>确认交班</el-button>
					<el-button :loading="loading" @click="print" round>打印小票</el-button>
				</div>
			</div>
			<div class="menu-button fixed" v-if="!is_pad" flex="dir:right"> 
				<el-button :loading="loading" @click="getOff" class="money" round>确认交班</el-button>
				<el-button :loading="loading" @click="print" round>打印小票</el-button>
			</div>
		</div>
	</div>
	<!-- 选择导购员 -->
	<el-dialog title="选择导购员" :visible.sync="chooseSaleVisible" class="sale-dialog" width="32%">
		<div>
			<div style="margin-bottom: 20px;" class="el-message-box__content">
				<div class="el-message-box__status el-icon-warning"></div>
				<div class="el-message-box__message">
					<p>确认选择该导购员？</p>
				</div>
			</div>
			<div v-if="sale" class="sale-info">
				<div>导购员编号<span>{{sale.number}}</span></div>
				<div>姓名<span>{{sale.name}}</span></div>
				<div>电话<span>{{sale.mobile}}</span></div>
				<div flex="cross:top">头像 
					<img :src="sale.head_url? sale.head_url : 'statics/img/app/user-default-avatar.png'" alt="">
				</div>
				<div>门店<span>{{sale.store_name}}</span></div>
			</div>
		</div>
		<span slot="footer" class="dialog-footer">
			<el-button size="small" @click="chooseSaleVisible = false">取 消</el-button>
			<el-button size="small" type="primary" @click="chooseSaleVisible = false;saleIndex = openSaleIndex">确 定</el-button>
		</span>
	</el-dialog>
	<!-- 修改密码 -->
	<el-dialog class="change-password" title="修改密码" :visible.sync="passwordVisible" width="32%">
		<el-form style="width: 85%;margin: 0 auto" ref="passwordForm" :model="passwordForm" :rules="rules" label-width="80px">
			<el-form-item label="旧密码" prop="password_old">
				<el-input @focus="inputStatus=true" @blur="inputStatus=false"type="password" size="small" placeholder="请输入旧密码" maxlength="16" v-model="passwordForm.password_old" autocomplete="off"></el-input>
			</el-form-item>
			<el-form-item label="新密码" prop="password">
				<el-input @focus="inputStatus=true" @blur="inputStatus=false"type="password" size="small" placeholder="请输入新密码" maxlength="16" v-model="passwordForm.password" autocomplete="off"></el-input>
			</el-form-item>
			<el-form-item label="确认密码" prop="password_verify">
				<el-input @focus="inputStatus=true" @blur="inputStatus=false"type="password" size="small" placeholder="请再次输入密码" maxlength="16" v-model="passwordForm.password_verify" autocomplete="off"></el-input>
			</el-form-item>
		</el-form>
		<span slot="footer" class="dialog-footer">
			<el-button size="small" @click="togglePassword">取 消</el-button>
			<el-button size="small" type="primary" :loading="passwordLoading" @click="submitChangePassword('passwordForm')">确 定</el-button>
		</span>
	</el-dialog>
	<!-- 成功提示 -->
	<el-dialog class="success-dialog" top="18%" :visible.sync="successVisible" @close="closeSuccess" width="20%">
		<div flex="dir:left cross:center">
			<img v-if="successVisible && !failVisible" style="margin-right: 14px" width="30px" height="30px" src="./../plugins/teller/assets/img/success.png" alt="">
			<img v-if="successVisible && failVisible" style="margin-right: 14px" width="30px" height="30px" src="./../plugins/teller/assets/img/fail.png" alt="">
			<div>{{successMsg}}</div>
		</div>
		<div v-if="successMsg == '充值成功！' && recharge && !inputRecharge">
			<div style="margin: 30px 0 8px;">本次充值奖励：</div>
			<div class="recharge-item-content">
				<span class="not-important">送</span>
				<span v-if="recharge.send_type & 0b00000001 && recharge.send_price"><span>{{recharge.send_price}}</span>余额</span>
				<span v-if="recharge.send_type & 0b00000010"><span>{{recharge.send_integral}}</span>积分</span>
				<span v-if="recharge.send_type & 0b00001000 && recharge.send_coupon && recharge.send_coupon.length == 1">
					<span>{{recharge.send_coupon[0].type == 2 ? recharge.send_coupon[0].sub_price : recharge.send_coupon[0].discount }}</span>{{recharge.send_coupon[0].type == 2 ? '元' : '折'}}{{recharge.send_coupon[0].name}}{{recharge.send_coupon[0].num > 1 ? 'x' + recharge.send_coupon[0].num : ''}}
				</span>
				<span v-if="recharge.send_type & 0b00001000 && recharge.send_coupon && recharge.send_coupon.length > 1">优惠券x{{recharge.send_coupon_num}}</span>
				<span v-if="recharge.send_type & 0b00010000 && recharge.send_card && recharge.send_card.length == 1">{{recharge.send_card[0].name}}{{recharge.send_card[0].num > 1 ? 'x' + recharge.send_card[0].num : ''}}</span>
				<span v-if="recharge.send_type & 0b00010000 && recharge.send_card && recharge.send_card.length > 1">卡券x{{recharge.send_card_num}}</span>
				<span v-if="recharge.send_type & 0b00000100 && recharge.member && recharge.member.id > 0"><span>{{recharge.member.name}}</span></span>
				<span v-if="recharge.send_type & 0b00100000 && recharge.lottery_limit > 0 && is_plugin_show"><span>{{recharge.lottery_limit}}</span>次抽奖机会</span>
			</div>
		</div>
	</el-dialog>
	<el-dialog :title="remarkTitle" :visible.sync="remarkVisible" width="32%">
		<el-input @focus="inputStatus=true" @blur="inputStatus=false"type="textarea" :rows="5" placeholder="请填写备注内容" v-model="remark" autocomplete="off"></el-input>
		<span slot="footer" class="dialog-footer">
			<el-button size="small" @click="remarkVisible = false">取消</el-button>
			<el-button size="small" :loading="remarkLoading" type="primary" @click="submitRemark">确定</el-button>
		</span>
	</el-dialog>
	<!-- 选择用户 -->
	<el-dialog title="选择用户" :visible.sync="memberVisible" width="610">
		<div>
			<el-checkbox-group @change="chooseUser" class="user-list" v-model="memberId" size="medium">
				<el-checkbox-button class="user-item" v-for="item in memberList" :label="item.user_id" :key="item.user_id">
					<img class="avatar" :src="item.avatar" alt="">
					<div class="username">{{ item.nickname }}</div>
					<div>
						<img width="30" height="30" class="platform-img" :src="item.platform" alt="">
					</div>
				</el-checkbox-button>
			</el-checkbox-group>
		</div>
		<span slot="footer" class="dialog-footer">
			<el-button size="small" @click="memberVisible = false">取 消</el-button>
			<el-button size="small" type="primary" @click="chooseMember">确 定</el-button>
		</span>
	</el-dialog>
	<!-- 选择优惠 -->
	<el-dialog title="选择优惠" :visible.sync="couponVisible" width="460px">
		<div v-if="member">
			<div class="member-coupon">
				<div v-if="couponList.length > 0 && setting.is_coupon == 1">
					<div>优惠券</div>
					<div class="coupon-input">
						<div>
							<el-input @focus="inputStatus=true" @blur="inputStatus=false"@keyup.enter.native="CouponKeywordSearch" size="small" placeholder="请输入优惠券名称" v-model="couponKeyword" clearable @clear="CouponKeywordSearch">
								<el-button slot="append" icon="el-icon-search" @click="CouponKeywordSearch"></el-button>
							</el-input>
						</div>
					</div>
					<div class="coupon-list">
						<div v-if="couponListLength > 0">
							<div @click="chooseCoupon(item)" class="coupon-list-item" :class="item.id == temp_use_coupon.id ? 'active' :''" v-for="item in couponList" :key="item.id" v-if="item.show">
								<img v-if="item.id == temp_use_coupon.id" class="active" src="./../plugins/teller/assets/img/active.png" alt="">
								<img src="./../plugins/teller/assets/img/coupon.png">
								<div class="item-left" flex="dir:top main:center">
									<div class="coupon-price t-omit" v-if="item.coupon_data.type == 2">￥{{item.coupon_data.sub_price}}</div>
									<div class="coupon-price t-omit" v-else>{{item.coupon_data.discount}}折</div>
									<div style="font-size: 10px" class="t-omit" v-if="item.coupon_min_price > 0">满{{item.coupon_data.min_price}}可用</div>
									<div style="font-size: 10px" v-else>无门槛使用</div>
									<div style="font-size: 10px" v-if="item.coupon_data.discount_limit">优惠上限:￥{{item.coupon_data.discount_limit}}</div>
								</div>
								<div class="item-right" flex="dir:top main:center">
									<div class="item-name t-omit t-large t-large-color">{{item.coupon_data.name}}</div>
									<div class="t-small-color time-area">{{item.start_time}} - {{item.end_time}}</div>
									<div v-if="item.coupon_data.appoint_type == 3">全场通用</div>
									<div v-else-if="item.coupon_data.appoint_type == 4">仅限当面付活动使用</div>
									<div v-else-if="item.coupon_data.appoint_type == 5">仅限礼品卡使用</div>
									<div v-else>限品类</div>
								</div>
							</div>
						</div>
						<div v-else flex="dir:top main:center cross:center">
							<img width="150" height="112" src="./../plugins/teller/assets/img/no-coupon.png" alt="">
							<div style="font-size: 16px;margin-top: 24px;color: #999999;">暂无可用优惠券</div>
						</div>
					</div>
				</div>
				<div v-if="setting.is_integral == 1">
					<el-switch v-model="tempUseIntegral" :disabled="orderInfo.integral.use_num == 0" :active-text="'(可用积分:'+orderInfo.integral.use_num+')'" inactive-text="使用积分"></el-switch>
				</div>
			</div>
		</div>
		<span slot="footer" class="dialog-footer">
			<el-button size="small" @click="couponVisible = false">取 消</el-button>
			<el-button size="small" type="primary" @click="checkInMember(1)">确 定</el-button>
		</span>
	</el-dialog>
	<!-- 优惠明细 -->
	<el-dialog title="优惠明细" :visible.sync="discountVisible" width="320px">
		<div style="margin-top: -20px;font-size: 16px;">
			<div style="margin-bottom: 5px;" flex="main:justify" v-if="orderInfo.integral && orderInfo.integral.use">
				<div style="color: #6c6c6c;">积分抵扣</div>
				<div style="color: #ff4544">-￥{{orderInfo.integral.deduction_price}}</div>
			</div>            <div style="margin-bottom: 5px;" flex="main:justify" v-if="orderInfo.coupon && orderInfo.coupon.use">
				<div style="color: #6c6c6c;">优惠券优惠</div>
				<div style="color: #ff4544">-￥{{orderInfo.coupon.coupon_discount}}</div>
			</div>
			<div style="margin-bottom: 5px;" v-if="+orderInfo.full_reduce_discount > 0" flex="main:justify">
				<div style="color: #6c6c6c;">满减优惠</div>
				<div style="color: #ff4544">-￥{{orderInfo.full_reduce_discount}}</div>
			</div>
			<div style="margin-bottom: 5px;" v-if="+orderInfo.member_discount > 0" flex="main:justify">
				<div style="color: #6c6c6c;">会员折扣</div>
				<div style="color: #ff4544">-￥{{orderInfo.member_discount}}</div>
			</div>
			<div style="margin-bottom: 5px;" v-if="+orderInfo.vip_discount > 0" flex="main:justify">
				<div style="color: #6c6c6c;">超级会员卡</div>
				<div style="color: #ff4544">-￥{{orderInfo.vip_discount}}</div>
			</div>
			<div style="margin-bottom: 5px;" v-if="+orderInfo.total_change_price > 0" flex="main:justify">
				<div style="color: #6c6c6c;">订单改价</div>
				<div style="color: #ff4544">{{orderInfo.change_price_type == 'subtract' ? '-': '+'}}￥{{orderInfo.total_change_price}}</div>
			</div>
			<div style="margin-bottom: 5px;" v-if="+orderInfo.erase_price > 0 || +orderInfo.erase_price < 0" flex="main:justify">
				<div style="color: #6c6c6c;">抹零</div>
				<div style="color: #ff4544">{{orderInfo.erase_price > 0 ? '+':'-'}}￥{{orderInfo.erase_price > 0 ? orderInfo.erase_price : -orderInfo.erase_price}}</div>
			</div>
		</div>
	</el-dialog>
</div>
<script>
	const app = new Vue({
		el: '#app',
		data() {
			let checkPassword = (rule, value, callback) => {
				if (!this.passwordForm.password_verify) {
					callback(new Error('请再次输入密码'))
				}else if (this.passwordForm.password != this.passwordForm.password_verify) {
					callback(new Error('两次密码不一致，请检查'))
				} else {
					callback();
				}
			};
			return {
                is_plugin_show: "<?= $is_plugin_show ?>",
				passwordInputName: 'password',
				msgbox: false, // 支付消息
				is_pad: false, // 平板模式
				addMoney: false, // 加钱页面
				changePrice: false, // 改价页面
				addCredit: false, // 会员充值
				loginMember: false,  // 登录会员页面
				chooseRecharge: false, // 选择充值方案
				is_cashier: false,  // 收银员个人信息
				useIntegral: false, // 是否使用积分
				tempUseIntegral: false, // 是否使用积分
				is_order: false, // 是否是订单

				successPay: false, // 付款成功
				remarkVisible: false, // 备注页面
				passwordVisible: false, // 修改密码页面
				successVisible: false, // 成功弹窗
				failVisible: false, // 失败弹窗
				discountVisible: false, // 优惠信息弹窗
				chooseSaleVisible: false, // 选择优惠弹窗
				memberVisible: false, // 选择会员弹窗
				couponVisible: false, // 选择优惠券弹窗
				setPassword: false, // 设置支付密码
				verifyPassword: false, // 校验支付密码
				stopGoods: false, // 停止加载
				stopFullGoods: false,
				stopOrder: false,
				inputStatus: false, // 输入框激活

				loading: false, // 页面loading
				passwordLoading: false, // 改密loading
				memberLoading: false, // 会员加载loading
				countLoading: false, // 合计loading
				couponLoading: false, // 优惠券加载loading
				payloading: false, // 支付loading
				remarkLoading: false, // 提交备注loading
				refundLoading: false, // 提交售后loading

				fullReduce: null, // 满减活动信息
				fullList: [], // 满减商品列表

				pay_password: '', // 第一遍支付密码
				verify_pay_password: '', // 第二遍支付密码
				successMsg: '', // 成功弹窗提示文字
				remarkTitle: '', // 备注弹窗标题
				remark: '', // 备注
				refundRemark: '',  // 售后备注
				keyword: '', // 商品搜索关键词
				orderKeyword: '', // 订单搜索关键词
				fullKeyword: '', // 满减商品搜索关键词
				couponKeyword: '', // 优惠券搜索关键词
				barcode: '', // 支付条形码
				memberBarcode: '', // 会员付款码
				payPassword: '', // 支付密码
				url: '',
				requestTime: '',
				goods_bar_code: '',
				goods_bar_code_list: [],
				hung_remark: '', // 挂单备注

				passwordForm: { // 改密表单
					password: '',
					password_verify: '',
					password_old: '',
				},
				rules: {  // 改密表单校验规则
					password: [
						{required: true, message: '请输入新密码'},
						{min: 6, max: 16, message: "密码长度在6-16个字符内"},
					],
					password_verify: [
						{validator: checkPassword, trigger: 'blur'},
					],
					password_old: [
						{required: true, message: '请输入旧密码'},
						{min: 6, max: 16, message: "密码长度在6-16个字符内"},
					],
				},
				showHung: false, // 取单
				sale: null, // 导购员
				tabIndex: -1, // 分类序号
				openSaleIndex: -1, // 点击查看导购员信息的序号
				step: 0, // 付款流程进度
				changeType: 1, // 改价方向 1 加钱 2 减钱
				refundType: 3, // 售后类型 1.退货退款|3.仅退款
				changeMoney: 0, // 修改的金额
				orderIndex: -1, // 订单序号
				couponListLength: 0, // 优惠券显示数量
				goodsPage: 1,
				fullGoodsPage: 1,
				orderPage: 1,
				totalCountNumber: 0, // 结算清单数量

				tabList: [], // 分类列表
				list: [], // 商品列表
				count: [], // 结算清单
				hungOrder: [], // 挂单订单列表
				order: [], // 订单列表

				creditType: 1, // 会员充值余额方式 1、按方案 2、自己填
				balanceType: 1, // 会员余额支付方式 1、动态付款码 2、余额支付密码
				activeTab: 0, // 当前的分页 0、商品 1、会员 2、订单 3、满减
				payPrice: 0, // 会员余额金额
				rechargeId: 0, // 选中的充值方案
				number: 1, // 当前商品数量
				cashier: {}, //收银员
				cashierInfo: null, // 收银员基本情况
				mall: {}, // 商城信息
				refundPrice: '', // 退款金额
				totalPrice: '0.00', // 订单总价
				noChangePrice: 0, // 未改价前价格
				getMoney: 0, // 实付金额
				salePrice: '0.00', // 折扣金额
				addPrice: 0, // 加钱的数量
				goods: null, // 商品详情
				orderDetail: null, // 订单详情
				refundDetail: null, // 售后详情
				selectAttr: null, // 选择的规格
				mobile: '', // 手机号
				member: null, // 会员
				memberId: null, // 会员详情
				temp: null, // 加钱商品
				use_coupon: { // 要使用的优惠券
					id: null
				},
				temp_use_coupon: { // 临时存
					id: null 
				},
				sales: [], // 导购列表
				memberList: [], // 会员列表
				couponList: [], // 优惠券列表
				rechargeList: [], // 充值发布方案
				saleIndex: -1, // 选择导购
				payment_type: '', // 付款方式
				hungLength: 0, // 挂单的列表长度
				inputRecharge: false, // 充值金额
				setting: null, // 设置详情
				numberWidth: 1920, // 页面宽度
				orderInfo: { // 订单信息
 					integral: {
						use_num: 0
					}
				},
				mode: 'search', // 登录模式
				loginMember: false, // 登录会员
				workDetail: null, // 工作详情
				stepTab: null, //结算步骤切换分页
				screenWidth: document.body.clientWidth,
				screenHeight: document.body.clientHeight,
				recharge: null,
			};
		},
		created: function () {
			this.loading = true;
			document.title = `<?= \Yii::$app->mall->name ? \Yii::$app->mall->name : '商城' ?>收银系统`;
			this.getSetting();
		},
		mounted() {
			window.addEventListener("keyup", this.inputCode);
			window.onresize = () => {
		      	return (() => {
		        	this.screenWidth = document.body.clientWidth
		        	this.screenHeight = document.body.clientHeight
		      	})()
		    }
		},
		destroyed() {
			window.removeEventListener("keydown", this.keyDown, false);
		},
        computed: {
            head_height() {
            	let height = 70;
            	if(this.is_pad) {
            		height = this.screenHeight*0.0573
            	}
                return height;
            },
            view_bottom() {
            	let bottom = (this.screenHeight - this.head_height) * 0.0859;
            	if(this.is_pad) {
            		bottom += 10;
            	}else {
            		bottom += 20
            	}
            	return bottom
            },
            view_other_bottom() {
            	let bottom = this.view_bottom;
            	if(this.is_pad && this.step > 1 && ! this.stepTab) {
            		bottom += 81;
            	}
            	return bottom;
            }
        },
		watch: {
			screenWidth: {
				immediate: true,
				handler(newValue) {
					this.numberWidth = newValue;
					if(newValue < 1601) {
						this.is_pad = true;
					}else {
						this.is_pad = false;
					}
				}
			},
			screenWidth: {
				immediate: true,
				handler(newValue) {
					this.numberWidth = newValue;
					if(newValue < 1601) {
						this.is_pad = true;
					}else {
						this.is_pad = false;
					}
				}
			}
		},
		methods: {
			getInputSataus(e) {
				this.inputStatus = e;
			},
			inputCode(e) {
				console.log(e.timeStamp,e.key)
				if(!this.inputStatus) {
					let para = {
						key: e.key,
						timeStamp: e.timeStamp
					}
					if(this.goods_bar_code_list[0] && e.timeStamp - this.goods_bar_code_list[0].timeStamp < 15) {
						if(e.key == 'Enter') {
							this.searchBarCode();
						}else if(e.key > -1 && e.key.length == 1) {
							this.goods_bar_code_list.unshift(para);
						}
					}else if(e.key > -1 && e.key.length == 1) {
						this.goods_bar_code_list = [para]
					}else {
						this.goods_bar_code_list = [];
					}
				}
			},
			searchBarCode() {
				if(this.goods_bar_code_list == []) {
					return false;
				}
				for(let item of this.goods_bar_code_list) {
					this.goods_bar_code += item.key;
				}
				if(!this.goods_bar_code) {
					return false;
				}else {
					this.goods_bar_code = this.goods_bar_code.split('').reverse().join('');
				}
				request({
					params: {
						r: 'plugin/teller/web/goods/bar-code-search',
						bar_code: this.goods_bar_code
					},
				}).then(e => {
					this.goods_bar_code = '';
					this.goods_bar_code_list = [];
					if (e.data.code === 0) {
						this.totalCountNumber++;
						let goods = e.data.data.goods;
						let same = false;
						if(this.count.length > 0) {
							for(let item of this.count) {
								if(item.goods_attr_id == goods.goods_attr_id) {
									same = true;
									item.num++;
								}
							}
						}
						if(!same) {
							this.count.push(goods);
						}
						setTimeout(()=>{
							this.previewOrder();
						},0)
					} else {
						this.$message.error(e.data.msg);
					}
				})
			},
			// 重新搜索
			reSearch() {
				this.member=null;
				this.mobile='';
				this.previewOrder();
			},
			// 清空订单状态
			clearOrder() {
				if(this.successMsg != '充值成功！') {
					this.saleIndex = -1;
					this.successPay = !this.successPay;
					this.count = [];
					this.totalCountNumber = 0;
					this.totalPrice = '0.00';
					this.salePrice = '0.00';
					this.member = null;
					this.sale = null;
					this.changePrice = false;
					this.showHung = false;
					this.hungOrder = [];
					this.addMoney = false;
					this.changeMoney = 0;
					this.memberBarcode = '';
					this.hung_remark = '';
					this.orderInfo = {
	 					integral: {
							use_num: 0
						}
					}
					this.use_coupon = {
						id: null
					};
					this.mode = 'search';
					this.useIntegral = false;
					localStorage.removeItem("list");
				}
				this.temp_use_coupon = {
					id: null
				}
				this.tempUseIntegral = false;
				this.step = 0;
				this.getMoney = 0;
				this.payment_type = this.setting.payment_type[0];
				this.addCredit = false;
				this.inputRecharge = false;
				this.chooseRecharge = false;
				this.barcode = '';
				this.successMsg = '';
				this.creditType = 1;
				this.payPrice = 0;
				this.getList();
			},
			// 去登录会员
			toLoginMember() {
				this.loginMember = true;
				this.$nextTick(() => {
					if(!this.is_pad) {
						this.$refs.member.$refs.member.focus();
					}
                });
			},
			// 显示底部功能页面
			toggleView(name) {
				if(!this.orderInfo.change_price_data && name == 'changePrice') {
					this.$message.error('暂不能改价');
					return false;
				}
				this.addMoney = false;
				this.showHung = false;
				this.hungOrder = [];
				this.step = 0;
				this.addCredit = false;
				this.changePrice = false;
				if(name == 'addMoney') {
					this.addMoney = true;
					this.$nextTick(() => {
						if(!this.is_pad) {
							this.$refs.add.$refs.add.focus();
						}
                    });
				}else if(name == 'changePrice') {
					this.changePrice = true;
					this.$nextTick(() => {
						if(!this.is_pad) {
							this.$refs.change.$refs.change.focus();
						}
                    });
				}else if(name == 'addCredit') {
					this.addCredit = true;
					this.step = 2;
				}else if(name == 'hung') {
					this.showHung = true;
				}
			},
			// 关闭成功提示
			closeSuccess() {
				this.successVisible = false;
				if(this.successMsg == '退款成功') {
					this.refundDetail = null;
					this.toOrderDetail(this.orderDetail.order_id);
					this.successMsg = '';
				}
				if(this.successMsg == '充值成功！') {
					this.step = 0;
					this.clearOrder();
					this.submitMember(this.member.mobile,this.member.user_id);
				}
				if(this.successMsg == '设置成功！') {
					this.setPassword = false;
					this.verifyPassword = false;
					this.pay_password = '';
					this.verify_pay_password = '';
				}
				if(this.successMsg == '交班成功') {
					this.$navigate({
                        r: this.url,
                        mall: this.mall.mall_id
                    });
				}
			},
			// 现金收款 输入实付金额
			getMoneyInput(e) {
				this.getMoney = e;
			},
			// 输入会员充值金额
			submitRecharge(e) {
				let price = +e;
				this.payPrice = price.toFixed(2);
				this.step = 3;
			},
			// 会员充值页面返回功能
			rechargeBack() {
				if(this.chooseRecharge) {
					this.chooseRecharge = false;
				}else if(this.inputRecharge) {
					this.inputRecharge = false;
				}else if(this.member) {
					this.member = null;
					this.mobile = '';
					this.loginMember = false;
					this.mode = 'search';
				}else {
					this.activeTab = 0;
					this.step = 0;
					this.addCredit = false;
				}
			},
			// 选中会员余额支付
			submitBalanceType() {
				if(this.balanceType == 1) {
					this.step = 5;
					this.$nextTick(() => {
						if(!this.is_pad) {
							this.$refs.barcode.focus();
						}
                    });
				}else {
					if(+this.member.balance < +this.totalPrice) {
						this.$confirm('余额不足，是否前往充值?', {
							confirmButtonText: '确定',
							cancelButtonText: '取消',
							type: 'error'
						}).then(() => {
							this.step = 2;
							this.addCredit=true;
							this.payment_type=this.setting.payment_type[0]
							this.changePrice=false;
							this.showHung=false;
							this.hungOrder=[];
							this.addMoney=false;
						})
					}else if(this.member.is_pay_password == 0) {
						this.$confirm('该会员暂未设置余额支付密码，是否前往设置?', {
							confirmButtonText: '设置密码',
							cancelButtonText: '暂不设置',
							type: 'success'
						}).then(() => {
							this.setPassword = true;
						})
					}else {
						this.step = 5;
					}
				}
			},
			toggleRecharge(item){
				this.rechargeId = item.id;
				this.payPrice = item.pay_price;
				this.recharge = item;
			},
			// 选中会员充值方案
			submitCreditType() {
				this.rechargeId = 0;
				this.payPrice = 0;
				if(this.creditType == 1) {
					this.chooseRecharge = true;
					this.loading = true;
					request({
						params: {
							r: 'plugin/teller/web/member/recharge-list',
						},
					}).then(e => {
						this.loading = false;
						if (e.data.code === 0) {
							this.rechargeList = e.data.data.list;
							if(this.rechargeList.length == 0) {
								this.$message.error('暂无充值方案');
							}
						} else {
							this.$message.error(e.data.msg);
						}
					})
				}else {
					this.recharge = null;
					this.inputRecharge = true;
					this.$nextTick(() => {
						if(!this.is_pad) {
							this.$refs.recharge.$refs.recharge.focus();
						}
                    });
				}
			},
			// 改价
			changeMoneyInput(e) {
				let msg = '';
				if(this.changeType == 1 && +e > +this.orderInfo.change_price_data.most_plus_end) {
					msg = '改价金额超出加价范围'
				}
				if(this.changeType == 2 && +e > +this.orderInfo.change_price_data.most_subtract_end) {
					msg = '改价金额超出减价范围'
				}
				if(msg) {
					this.$message.error(msg);
					return false;
				}else {
					this.changePrice = false;
					this.changeMoney = e;
					this.previewOrder();
					this.step = 0;
				}
			},
			// 加钱
			addMoneyGoods(e) {
				if(e > 0) {
					for(let item of this.count) {
						if(item.id == this.temp.id) {
							item.price = +item.price + +e;
							item.selectAttr.price = item.price.toFixed(2);
							this.addMoney = false;
							this.countPrice();
							return
						}
					}
					let price = +e;
					let selectAttr = JSON.parse(JSON.stringify(this.temp.attr[0]));
					selectAttr.price = price.toFixed(2);
					let para = {
						id: this.temp.id,
						attr: this.temp.attr_groups,
						selectAttr: selectAttr,
						num: 1,
						name: this.temp.name,
						goods_attr_id: this.temp.attr[0].id,
						cart_id: 0,
						cover_pic: this.temp.cover_pic,
						price: price
					}
					this.count.push(para);
					this.addMoney = false;
					this.countPrice();
					this.step = 0;
				}else {
					this.$message.error('请输入加钱金额');
				}
			},
			// 确认交班
			getOff() {
				this.loading = true;
				request({
					params: {
						r: 'plugin/teller/web/manage/off-duty',
					},
				}).then(e => {
					this.loading = false;
					if (e.data.code === 0) {
						this.successVisible = true;
						this.successMsg = e.data.msg;
						localStorage.removeItem("list");
						localStorage.removeItem("hung");
						this.url = e.data.data.url;
						setTimeout(()=>{
							this.$navigate({
	                            r: e.data.data.url,
	                            mall_id: e.data.data.mall_id
	                        });
						},2000)
					} else {
						this.$message.error(e.data.msg);
					}
				})
			},
			// 打印小票
			print() {
				request({
					params: {
						r: 'plugin/teller/web/manage/print',
					}
				}).then(e =>{
					if (e.data.code != 0) {	
						this.$message.error(e.data.msg);
					}
				})
			},
			// 获取交班前信息
			transition() {
				this.loading = true;
				request({
					params: {
						r: 'plugin/teller/web/manage/work-log',
					},
				}).then(e => {
					this.loading = false;
					if (e.data.code === 0) {
						this.is_cashier = false;
						this.workDetail = e.data.data.order_info;
						this.cashierInfo = e.data.data.cashier_info;
					} else {
						this.$message.error(e.data.msg);
					}
				})
			},
			// 切换分类
			chooseCatTab(index) {
				if(index == this.tabIndex + 1) {
					return false;
				}
				if(this.step == 0) {
					this.tabIndex = index - 1;
					if(this.activeTab == 0) {
						this.stopGoods = false;
						this.list = [];
						this.getList();
					}
					if(this.activeTab == 3) {
						this.stopFullGoods = false;
						this.fullList = [];
						this.getFullList();
					}
				}
			},
			// 选中会员及优惠
			checkInMember(type) {
				if(type === 1) {
					this.use_coupon = this.temp_use_coupon;
					this.useIntegral = this.tempUseIntegral;
				}
				this.couponVisible = false;
				this.previewOrder();
				if(this.loginMember) {
					this.loginMember = false;
				}else if(this.step > 0) {
					if(this.setting.payment_type.length == 0) {
						this.$message.error('请先在后台进行支付设置');
						return false;
					}
					this.step = 3;
				}else {
					this.activeTab = 0;
				}
			},
			// 选择会员
			chooseMember() {
				for(let item of this.memberList) {
					if(item.user_id == this.memberId[0]) {
						this.member = item;
						this.mobile = item.mobile;
						this.memberVisible = false;
						this.previewOrder();
						if(!this.addCredit) {
							this.toCouponSearch();
						}
						return
					}
				}
			},
			// 优惠券搜索
			toCouponSearch() {
				this.couponLoading = true;
				let form_data = 
				{
					mch_id:0,
					goods_list: this.count,
					distance:0,
					remark: '',
					order_form:[],
					use_integral:this.useIntegral ? 1 : 0,
					user_coupon_id: this.use_coupon.id > 0 ? this.use_coupon.id : 0,
					user_id: this.member.user_id,
					add_money: this.addPrice,
					change_price_type: this.changeType == 1 ? 'add' : 'subtract',
					change_price: this.changeMoney,
					payment_type: this.payment_type
				}
				request({
					params: {
						r: 'plugin/teller/web/order/coupon',
					},
					data: {
						form_data: JSON.stringify(form_data),
						is_cant_use_list: 0
					},
					method: 'post',
				}).then(e => {
					this.couponLoading = false;
					if (e.data.code === 0) {
						this.couponList = e.data.data.list;
						for(let item of  this.couponList) {
							item.show = true;
						}
						this.couponListLength = this.couponList.length
					} else {
						this.$message.error(e.data.msg);
					}
				})
			},
			CouponKeywordSearch() {
				this.couponListLength = 0;
				for(let item of  this.couponList) {
					item.show = false;
					if(!this.couponKeyword || item.coupon_data.name.indexOf(this.couponKeyword) > - 1) {
						item.show = true;
						this.$forceUpdate();
						this.couponListLength++
					}
				}
			},
			chooseCoupon(item) {
				let para = {
					id: null
				}
				this.temp_use_coupon = this.temp_use_coupon.id != item.id ? item : para
			},
			// 搜索会员
			submitMember(res,user_id) {
				if(!res) {
					return false
				}else if(res.length != 11 && res.length != 16) {
					this.$message.error('请输入完整手机号');
					return false;
				}
				this.memberLoading = true;
				request({
					params: {
						r: 'plugin/teller/web/member/index',
						keyword: res
					},
					method: 'get',
				}).then(e => {
					if (e.data.code === 0) {
						if(e.data.data.list.length == 0) {
							this.memberLoading = false;
							this.mobile = '';
							this.$message.error('未找到相关会员');
						}else if(e.data.data.list.length == 1) {
							this.memberLoading = false;
							this.member = e.data.data.list[0];
							if(this.member.mobile != res) {
								this.mode = 'scan';
								this.memberBarcode = res.trim();
							}
							this.mobile = this.member.mobile;
							if(!user_id && this.count.length > 0) {
								this.previewOrder();
								if(!this.addCredit) {
									this.toCouponSearch();
								}
							}
						}else if(user_id) {
							this.memberLoading = false;
							let member = e.data.data.list;
							for(let item of member) {
								if(user_id == item.user_id) {
									this.member = item;
									this.mobile = this.member.mobile
								}
							}
						}else {
							this.memberLoading = false;
							this.memberList = e.data.data.list;
							this.memberId = [e.data.data.list[0].user_id];
							this.memberVisible = true;
						}
					} else {
						this.mobile = '';
						this.$message.error(e.data.msg);
					}
				}).catch(e => {
					console.log(e);
				});
			},
			// 显示优惠
			useDiscount() {
				if(this.count.length == 0) {
					this.$message.error('请先将商品添加到结算清单');
					return false;
				}
				this.temp_use_coupon = this.use_coupon;
				this.tempUseIntegral = this.useIntegral;
				this.couponVisible = true;
			},
			// 选择某个用户为当前会员
			chooseUser(e) {
				this.memberId = [e.pop()]
			},
			// 取单
			addCount(idx) {
				if(this.hungOrder[idx].member && this.member && this.hungOrder[idx].member.user_id != this.member.user_id) {
					this.successVisible = true;
					this.failVisible = true;
					this.successMsg = '会员信息不一致，无法取单!'
				}else {
					if(this.count.length == 0) {
						this.totalPrice = this.hungOrder[idx].total ? this.hungOrder[idx].total : '0.00';
						this.count = this.hungOrder[idx].list;
						this.clearhungOrder(idx);
					}else {
						this.$confirm('当前有进行中的收银，是否合并订单?', '提示', {
				          	confirmButtonText: '确认',
				        }).then(() => {
							let countList = JSON.parse(JSON.stringify(this.count));
							for(let item of this.hungOrder[idx].list) {
								for(let index in countList) {
									console.log(item.goods_attr_id == countList[index].goods_attr_id)
									if(item.goods_attr_id == countList[index].goods_attr_id) {
										this.count[index].num = +countList[index].num + +item.num
		                				this.$forceUpdate();
									}else {
										this.count.push(item)
									}
								}
							}
							this.clearhungOrder(idx);
				        }).catch(() => {
				        	return false;
				        });
					}
				}
			},
			clearhungOrder(idx) {
					let para = {
						id: null
					}
					this.successVisible = true;
					this.failVisible = false;
					this.successMsg = '取单成功!';
					setTimeout(()=>{
						if(this.successVisible) {
							this.successVisible = false;
							this.successMsg = '';
						}
					},5000)
					this.showHung = false;
					this.changeType = this.hungOrder[idx].changeType ? this.hungOrder[idx].changeType : (this.setting.is_goods_change_price_type == 1 && this.setting.most_plus) || (this.setting.is_goods_change_price_type == 2 && this.setting.most_plus_percent) ? 1 : 2;
					this.changeMoney = this.hungOrder[idx].changeMoney ? this.hungOrder[idx].changeMoney : 0;
					this.member = this.hungOrder[idx].member ? this.hungOrder[idx].member : null;
					this.hung_remark = this.hungOrder[idx].remark ? this.hungOrder[idx].remark : '';
					this.sale = this.hungOrder[idx].sale ? this.hungOrder[idx].sale : null;
					this.saleIndex = this.hungOrder[idx].saleIndex ? this.hungOrder[idx].saleIndex : -1;
					this.use_coupon = this.hungOrder[idx].use_coupon ? this.hungOrder[idx].use_coupon : para;
					this.use_integral = this.hungOrder[idx].use_integral ? this.hungOrder[idx].use_integral : false;
					this.hungOrder.splice(idx,1);
					let list = JSON.stringify(this.hungOrder)
					localStorage.setItem('hung',list);
					this.hungLength--;
					this.step = 0;
					if(this.goods) {
						this.toDetail(this.goods)
					}else {
						if(this.activeTab == 0) {
							this.stopGoods = false;
							this.list = [];
							this.getList();
						}
						if(this.activeTab == 3) {
							this.stopFullGoods = false;
							this.fullList = [];
							this.getFullList();
						}
					}
					this.countPrice();
					if(this.member) {
						this.toCouponSearch();
					}
			},
			// 显示订单详情
			toOrderDetail(id) {
				this.loading = true;
				request({
					params: {
						r: 'plugin/teller/web/order/order-show',
						order_id: id
					},
				}).then(e => {
					this.loading = false;
					if (e.data.code === 0) {
						this.orderDetail = e.data.data.detail
					} else {
						this.$message.error(e.data.msg);
					}
				})
			},
			// 挂起的订单提交备注
			submitRemark() {
				if(this.is_order) {
					this.remarkLoading = true;
					request({
						params: {
							r: 'plugin/teller/web/order/seller-remark'
						},
						data: {
							order_id: this.order[this.orderIndex].order_id,
							seller_remark: this.remark
						},
						method: 'post',
					}).then(e => {
						this.remarkLoading = false;
						if (e.data.code === 0) {
							this.remarkVisible = false;
							this.order[this.orderIndex].seller_remark = this.remark;
						} else {
							this.$message.error(e.data.msg);
						}
					})
				}else {
					this.remarkVisible = false;
					this.hungOrder[this.orderIndex].remark = this.remark;
					let list = JSON.stringify(this.hungOrder)
					localStorage.setItem('hung',list);
				}
			},
			// 挂起的订单添加备注
			addRemark(idx, mode) {
				this.remarkVisible = true;
				if(mode == 'order') {
					this.is_order = true;
					this.remark = this.order[idx].seller_remark ? this.order[idx].seller_remark : '';
					this.remarkTitle = this.order[idx].seller_remark ? '修改备注':'添加备注';
				}else {
					this.is_order = false;
					this.remark = this.hungOrder[idx].remark ? this.hungOrder[idx].remark : '';
					this.remarkTitle = this.hungOrder[idx].remark ? '修改备注':'添加备注';
				}
				this.orderIndex = idx;
			},
			// 删除挂单
			delHung(idx) {
				this.$confirm('确认删除当前订单数据？', {
					confirmButtonText: '确定',
					cancelButtonText: '取消',
					type: 'warning'
				}).then(() => {
					this.hungOrder.splice(idx,1);
					let list = JSON.stringify(this.hungOrder)
					localStorage.setItem('hung',list);
					this.hungLength--;
					if(this.hungLength == 0) {
						this.showHung = false;
					}
					if(this.goods) {
						this.toDetail(this.goods)
					}else {
						if(this.activeTab == 0) {
							this.stopGoods = false;
							this.list = [];
							this.getList();
						}
						if(this.activeTab == 3) {
							this.stopFullGoods = false;
							this.fullList = [];
							this.getFullList();
						}
					}
				}).catch(() => {
					this.$message({
						type: 'info',
						message: '已取消删除'
					});          
				});
			},
			// 挂单
			toHung() {
				if(this.count.length == 0) {
					this.$message.error('暂无可挂订单');
					return false;
				}
				let list = localStorage.getItem('hung') ? JSON.parse(localStorage.getItem('hung')) : [];
				let para = {
					list: this.count,
					detail: this.count,
					total: this.totalPrice,
					member: this.member,
					sale: this.sale,
					saleIndex: this.saleIndex,
					changeType: this.changeType,
					changeMoney: this.changeMoney,
					use_coupon: this.use_coupon,
					use_integral: this.use_integral,
					remark: this.hung_remark
				}
				list.push(para)
				list = JSON.stringify(list)
				localStorage.setItem('hung',list);
				setTimeout(()=>{
					localStorage.removeItem("list");
					window.location.reload();
				})
			},
			// 显示挂单列表
			getOrder() {
				if(this.hungLength == 0) {
					this.$message.error('暂无可取订单');
					return false;
				}
				this.toggleView('hung');
				this.hungOrder = JSON.parse(localStorage.getItem('hung'));
			},
			showInfo() {
				this.is_cashier = true;
				this.cashierInfo = null;
				this.workDetail = null
			},
			// 退出
			loginout() {
				this.$confirm('确认退出？', '提示', {
					confirmButtonText: '确定',
					cancelButtonText: '取消',
					type: 'warning'
				}).then(() => {
					this.loading = true;
					request({
						params: {
							r: 'plugin/teller/web/passport/logout',
						}
					}).then(e => {
						this.loading = false;
						if (e.data.code === 0) {
							localStorage.removeItem("list");
							setTimeout(()=>{
								this.$navigate({
		                            r: e.data.data.url,
		                            mall_id: e.data.data.mall_id
		                        });
							},500)
						} else {
							this.$message.error(e.data.msg);
						}
					})
				})
			},
			// 修改密码
			togglePassword() {
				this.passwordVisible = !this.passwordVisible;
				this.passwordForm.password = '';
				this.passwordForm.password_verify = '';
				this.passwordForm.password_old = '';
				this.$nextTick(() => {
					if(!this.is_pad) {
						this.$refs.passwordForm.resetFields();
					}
				})
			},
			// 提交修改密码
			submitChangePassword(formName) {
				this.$refs[formName].validate((valid) => {
					if (valid) {
						this.passwordLoading = true;
						request({
							params: {
								r: 'plugin/teller/web/manage/update-password',
							},
							data: {
								password: this.passwordForm.password,
								password_verify: this.passwordForm.password_verify,
								password_old: this.passwordForm.password_old,
							},
							method: 'post',
						}).then(e => {
							this.passwordLoading = false;
							if (e.data.code === 0) {
								this.togglePassword();
								this.successVisible = true;
								this.failVisible = false;
								this.successMsg = '修改成功！'
							} else {
								this.$message.error(e.data.msg);
								this.$nextTick(() => {
									if(!this.is_pad) {
										this.$refs.passwordForm.resetFields();
									}
								})
							}
						})
					}
				})
			},
			// 预览订单
			previewOrder() {
				if(this.count.length == 0) {
					return false;
				}
				this.countLoading = true;
				let list = [
					{
						mch_id:0,
						goods_list: this.count,
						distance:0,
						remark: '',
						order_form:[],
						use_integral: this.useIntegral ? 1 : 0,
						user_coupon_id: this.use_coupon.id > 0 ? this.use_coupon.id : 0
					}
				];
				let form_data = {
					list: list,
					address_id: 0,
					sales_id: this.saleIndex > -1 ? this.sales[this.saleIndex].id : '',
					user_id: this.member ? this.member.user_id : '',
					add_money: this.addPrice,
					change_price_type: this.changeType == 1 ? 'add' : 'subtract',
					change_price: this.count.length > 0 ? this.changeMoney : 0,
					payment_type: this.payment_type,
					time: (new Date()).valueOf()
				}
				request({
					params: {
						r: 'plugin/teller/web/order/preview',
					},
					data: {
						form_data: JSON.stringify(form_data)
					},
					method: 'post',
				}).then(e => {
					this.countLoading = false;
					this.memberLoading = false;
					if (e.data.code === 0) {
						if(e.data.data.time < this.requestTime) {
							return false;
						}
						this.requestTime = e.data.data.time;
						this.orderInfo = e.data.data.mch_list[0];
						this.totalPrice = e.data.data.total_price;
						this.changeMoney = this.orderInfo.total_change_price; 
						this.noChangePrice = this.orderInfo.total_change_price ? this.changeType == 1 ? (+this.orderInfo.total_goods_price - +this.orderInfo.total_change_price).toFixed(2) : (+this.orderInfo.total_goods_price + +this.orderInfo.total_change_price).toFixed(2) : this.totalPrice
						this.salePrice = this.orderInfo.total_discounts_price > 0 ? '-￥' + this.orderInfo.total_discounts_price : this.orderInfo.total_discounts_price < 0 ? '+￥' + (-this.orderInfo.total_discounts_price).toFixed(2) : '￥0.00';
						if(this.step == 5 && this.payment_type == 'balance' && this.memberBarcode && this.balanceType == 1) {
							this.submitPay();
						}
					} else {
						this.$message.error(e.data.msg);
						this.count = JSON.parse(localStorage.getItem('handle'));
						let list = JSON.stringify(this.count)
						localStorage.setItem('list',list);
					}
				})
			},
			// 提交会员充值订单
			submitMemberOrder() {
				if(!this.barcode && (this.payment_type == 'alipay_scan' || this.payment_type == 'wechat_scan')) {
					this.$message.error('请使用扫码枪扫描客户付款码');
					return false;
				}
				this.payloading = this.$loading({
					lock: true,
					text: '支付中',
					spinner: 'el-icon-loading',
					background: 'rgba(0, 0, 0, 0.7)'
				});
				let para = {
					pay_type: this.payment_type,
					sales_id: this.saleIndex > -1 ? this.sales[this.saleIndex].id : '',
					user_id: this.member ? this.member.user_id : this.setting.user_id,
				}
				if(this.creditType == 1) {
					para.id = this.rechargeId
				}else {
					para.pay_price = this.payPrice
				}
				request({
					params: {
						r: 'plugin/teller/web/order/recharge-order',
					},
					data: para,
					method: 'post',
				}).then(e => {
					if (e.data.code === 0) {
						if(this.payment_type == 'alipay_scan' || this.payment_type == 'wechat_scan') {
							this.getCodePay(e.data.data.pay_id)
						}else {
							let paraData = {
								id: e.data.data.pay_id,
								user_id: this.member ? this.member.user_id : this.setting.user_id,
								pay_type: this.payment_type
							}
							request({
								params: {
									r: 'plugin/teller/web/teller-payment/other-pay',
								},
								data: paraData,
								method: 'post',
							}).then(e => {
								this.payloading.close();
								if (e.data.code === 0) {
									this.successVisible = true;
									this.failVisible = false;
									this.successMsg = '充值成功！'
									setTimeout(()=>{
										if(this.successVisible) {
											this.recharge = null;
											this.closeSuccess();
										}
									},5000)
								} else {
									this.$message.error(e.data.msg);
								}
							})
						}
					} else {
						this.$message.error(e.data.msg);
						this.payloading.close();
					}
				})
			},
			// 提交订单
			submitOrder() {
				if(!this.barcode && (this.payment_type == 'alipay_scan' || this.payment_type == 'wechat_scan')) {
					this.$message.error('请使用扫码枪扫描客户付款码');
					return false;
				}
				this.payloading = this.$loading({
					lock: true,
					text: '支付中',
					spinner: 'el-icon-loading',
					background: 'rgba(0, 0, 0, 0.7)'
				});
				let list = [
					{
						mch_id:0,
						goods_list: this.count,
						distance:0,
						remark: '',
						order_form:[],
						use_integral: this.useIntegral ? 1 : 0,
						user_coupon_id: this.use_coupon.id > 0 ? this.use_coupon.id : 0
					}
				];
				let form_data = {
					list: list,
					address_id: 0,
					sales_id: this.saleIndex > -1 ? this.sales[this.saleIndex].id : '',
					user_id: this.member ? this.member.user_id : this.setting.user_id,
					add_money: this.addPrice,
					change_price_type: this.changeType == 1 ? 'add' : 'subtract',
					change_price: this.changeMoney,
					payment_type: this.payment_type
				}
				request({
					params: {
						r: 'plugin/teller/web/order/submit',
					},
					data: {
						form_data: JSON.stringify(form_data)
					},
					method: 'post',
				}).then(e => {
					if (e.data.code === 0) {
						this.checkSubmitOrder(e.data.data.queue_id,e.data.data.token)
					} else {
						this.$message.error(e.data.msg);
						this.payloading.close();
					}
				})
			},
			getCodePay(id, type) {
				this.barcode = this.barcode.trim()
				request({
					params: {
						r: 'plugin/teller/web/teller-payment/pay-data',
						id: id,
						user_id: this.member ? this.member.user_id : this.setting.user_id,
						pay_type: this.payment_type,
						auth_code: this.barcode
					},
					method: 'get',
				}).then(e => {
					if (e.data.code === 0) {
						this.payloading.close();
						this.clearOrder();
						this.$msgbox.close();
					} else {
						if(e.data.msg.indexOf('需要用户输入支付密码') > 0 || e.data.msg.indexOf('支付中，请稍后再查询支付结果') > 0 || e.data.msg.indexOf('order success pay inprocess') > 0) {
							if(!type) {
								this.msgbox = true;
								msg = this.$confirm('收款中', '提示', {
									showClose: false,
									closeOnPressEscape: false,
									closeOnClickModal: false,
						          	confirmButtonText: '确认支付',
						          	cancelButtonText: '取消支付',
						        }).then(() => {
									this.getCodePay(id, 'again');
						        }).catch(() => {
						        	this.cancelPay(id);
						        });
							}
							setTimeout(()=>{
								this.getCodePay(id, 'again');
							},5000)
						}else {
							this.barcode = '';
							if(this.msgbox) {
								this.$msgbox.close();
								this.msgbox = false;
							}
							this.payloading.close();
							this.$confirm(e.data.msg, '提示', {
								showCancelButton: false,
					          	confirmButtonText: '确认',
					        }).then(() => {
								this.cancelPay(id);
					        }).catch(() => {
								this.cancelPay(id);
					        });
						}
					}
				})
			},
			// 取消支付
			cancelPay(id) {
				request({
					params: {
						r: 'plugin/teller/web/order/order-cancel',
						payment_order_union_id: id,
						pay_type: this.payment_type
					},
				}).then(e => {
					this.payloading.close();
					this.barcode = '';
					if(this.payment_type == 'balance' && this.balanceType == 1) {
						this.memberBarcode =  '';
					}
					if (e.data.code != 0) {
						this.$message.error(e.data.msg);
						this.payloading.close();
					}
				})
			},
			// 检查订单提交队列情况
			checkSubmitOrder(queue_id,token) {
				let para = {
					queue_id: queue_id,
					token: token,
				}
				request({
					params: {
						r: 'plugin/teller/web/order/pay-data',
					},
					data: {
						queue_id: queue_id,
						token: token,
						user_id: this.member ? this.member.user_id : this.setting.user_id,
					},
					method: 'post',
				}).then(e => {
					if (e.data.code === 0) {
						if(e.data.data.retry && e.data.data.retry == 1) {
							this.checkSubmitOrder(queue_id,token);
						}else {
							if(this.payment_type == 'alipay_scan' || this.payment_type == 'wechat_scan') {
								this.getCodePay(e.data.data.id)
							}else {
								let paraData = {
									id: e.data.data.id,
									user_id: this.member ? this.member.user_id : this.setting.user_id,
									pay_type: this.payment_type
								}
								if(this.payment_type == 'balance') {
									paraData.balance_type = this.balanceType == 1 ? 'pay_code' : 'pay_password';
									if(this.balanceType == 1) {
										paraData.pay_code = this.memberBarcode.trim();
									}else {
										paraData.pay_password = this.payPassword
									}
								}
								request({
									params: {
										r: 'plugin/teller/web/teller-payment/other-pay',
									},
									data: paraData,
									method: 'post',
								}).then(e => {
									this.payloading.close();
									if (e.data.code === 0) {
										this.successMsg = '';
										this.clearOrder();
									} else {
										this.$message.error(e.data.msg);
									}
								})
							}
						}
					} else {
						this.payloading.close();
						this.barcode = '';
						if(this.payment_type == 'balance' && this.balanceType == 1) {
							this.memberBarcode =  '';
						}
						this.$message.error(e.data.msg);
					}
				})
			},
			// 校验支付密码
			checkPayPassword(pay_password) {
				if(pay_password.length < 6) {
					return false;
				}
				if(this.setPassword) {
					if(!this.verifyPassword) {
						this.pay_password = pay_password;
						this.verifyPassword = true;
					}else {
						this.verify_pay_password = pay_password;
						if(this.verify_pay_password != this.pay_password) {
							this.verifyPassword = false;
							this.$message.error('密码输入不一致');
							this.verify_pay_password = '';
							this.pay_password = '';
						}
						request({
							params: {
								r: 'plugin/teller/web/member/set-pay-password',
							},
							data: {
								user_id: this.member.user_id,
								pay_password: this.pay_password,
								verify_pay_password: this.verify_pay_password,
							},
							method: 'post',
						}).then(e => {
							if (e.data.code === 0) {
								this.successVisible = true;
								this.successMsg = '设置成功！';
								this.member.is_pay_password = 1;
								setTimeout(()=>{
									if(this.successVisible) {
										this.closeSuccess();
									}
								},5000)
							} else {
								this.verifyPassword = false;
								this.$message.error(e.data.msg);
								this.verify_pay_password = '';
								this.pay_password = '';
								this.payloading.close();
							}
						})
					}
				}else {
					this.payloading = this.$loading({
						lock: true,
						text: '支付中',
						spinner: 'el-icon-loading',
						background: 'rgba(0, 0, 0, 0.7)'
					});
					request({
						params: {
							r: 'plugin/teller/web/member/verify-pay-password',
						},
						data: {
							user_id: this.member.user_id,
							pay_password: pay_password,
						},
						method: 'post',
					}).then(e => {
						if (e.data.code === 0) {
							this.passwordInputName = 'password';
							this.payPassword = pay_password;
							this.submitPay();
						} else {
							this.payPassword = '';
							this.passwordInputName += this.passwordInputName
							this.$message.error(e.data.msg);
							this.payloading.close();
						}
					})
				}
			},
			// 提交付款
			submitPay() {
				console.log(1)
				if(this.payment_type == "cash" && !this.getMoney) {
					this.$message.error('请输入实付金额');
					return false;
				}
				if(this.addCredit) {
					if(this.payment_type == "cash" && +this.getMoney < +this.payPrice) {
						this.$message.error('实付金额小于应收金额');
						return false;
					}
					if(this.countLoading) {
						return false;
					}
					this.submitMemberOrder();
				}else {
					if(this.payment_type == "cash" && +this.getMoney < +this.totalPrice) {
						this.$message.error('实付金额小于应收金额');
						return false;
					}
					if(this.countLoading) {
						return false;
					}
					this.submitOrder();
				}
			},
			// 会员页面返回的多种情况
			memberBack() {
				if(this.stepTab > 0) {
					this.stepTab = null
				}else if(this.addCredit) {
					this.member = null;
					this.mobile = '';
					this.loginMember = false;
					this.mode = 'search';
				}else if(this.step == 2) {
					this.step--;
					if(this.sales.length == 0) {
						this.step--;
					}
				}else if(this.step == 0) {
					this.activeTab = 0
				}
			},
			// 返回
			goback() {
				if(this.stepTab > 0) {
					this.stepTab = null;
					return false;
				}
				if(this.refundDetail) {
					this.refundDetail = null;
					return false;
				}
				if(this.orderDetail) {
					this.orderDetail = null;
					return false;
				}
				this.goods = null;
				this.loginMember = false;
				if(this.step > 0) {
					this.step--;
				}else {
					this.activeTab = 0;
				}
			},
			// 下一步
			nextStep() {
				if(this.chooseRecharge && this.step != 3) {
					if(this.setting.payment_type.length == 0 && (this.setting.payment_type.length == 1 && this.setting.payment_type[0] == 'balance')) {
						this.$message.error('请先在后台进行支付设置');
						return false
					}
					if(this.rechargeId) {
						this.step = 3;
					}else {
						this.$message.error('请选择充值方案');
					}
					return false
				}
				if(this.step == 2 && this.setting.payment_type.length == 0) {
					this.$message.error('请先在后台进行支付设置');
					return false
				}
				if(this.step == 2 && !this.member && this.payment_type == 'balance') {
					if(this.setting.payment_type.length == 1) {
						this.$message.error('请先登录会员');
						return false;
					}else {
						for(let item of this.setting.payment_type) {
							if(item != 'balance') {
								this.payment_type = item;
								this.step = 3;
								return false
							}
						}
					}
				}
				this.step++;
				if(this.step == 2 && !this.member) {
					this.$nextTick(() => {
						if(!this.is_pad) {
							this.$refs.member.$refs.member.focus();
						}
                    });
				}
				if(this.step == 1 && this.sales.length == 0) {
					this.step = 2;
				}
				if(this.step == 2 && this.member && !this.addCredit) {
					this.step = 3;
					this.memberLoading = false;
				}
				if(this.step == 3 && this.member) {
					this.submitMember(this.member.mobile,this.member.user_id);
				}
				if(this.step == 4) {
					this.previewOrder();
					if(this.member) {
						this.submitMember(this.member.mobile,this.member.user_id);
					}
					if(this.payment_type == 'alipay_scan' || this.payment_type == 'wechat_scan') {
						this.$nextTick(() => {
							if(!this.is_pad) {
								this.$refs.barcode.focus();
	                    	}
	                    });
					}else if(this.payment_type == 'cash') {
						this.getMoney = this.addCredit ? +this.payPrice : +this.totalPrice
						this.$nextTick(() => {
							if(!this.is_pad) {
								this.$refs.cash.$refs.cash.focus();
	                    	}
	                    });
					}else if(this.payment_type == 'balance' && this.mode == 'scan') {
						this.step = 5;
						this.submitPay();
					}
				}
			},
			// 显示本次优化
			showSales(item, index) {
				this.chooseSaleVisible = true;
				this.sale = item;
				this.openSaleIndex = index;
			},
			// 进入收款流程
			nextSubmit(count) {
				if(!this.addCredit) {
					this.toggleView();
				}else {
					this.loginMember = false;
					this.step = 1;
					return false;
				}
				if(this.count.length == 0 && count == 'count') {
					this.$message.error('请先将商品添加到结算清单');
					return false;
				}
				if(this.step > 0) {
					return false;
				}
				if(this.sales && this.sales.length > 0) {
					this.step = 1;
					if(this.saleIndex > -1 && count == 'count') {
						this.step = 2;
						if(this.member) {
							if(this.setting.payment_type && this.setting.payment_type.length == 0) {
								this.$message.error('请先在后台进行支付设置');
								return false
							}
							this.step = 3;
						}
					}
				}else {
					if(this.member) {
						if(this.setting.payment_type && this.setting.payment_type.length == 0) {
							this.$message.error('请先在后台进行支付设置');
							return false
						}
						this.step = 3;
					}else {
						this.step = 2;
					}
				}
			},
			// 整单取消
			cancel() {
				if(this.count.length > 0) {
					this.$confirm('确认删除当前订单数据？', {
						confirmButtonText: '确定',
						cancelButtonText: '取消',
						type: 'warning'
					}).then(() => {
						this.step = 0;
						this.successPay = true;
						this.clearOrder();
					})
				}
			},
			// 操作挂单
			handleMenu(index,type) {
				if(this.count[index].id == this.temp.id && type != 'del') {
					return false;
				}
				if(this.step > 2) {
					return false;
				}
				let list = JSON.stringify(this.count)
				localStorage.setItem('handle', list);
				if(type == 'del') {
					this.count.splice(index,1);
					if(this.count.length == 0) {
						this.hung_remark = '';
					}
				}
				if(type == 'add') {
					if(this.count[index].num == this.count[index].selectAttr.stock) {
						return false;
					}
					this.count[index].num++;
					this.count[index].price = +this.count[index].selectAttr.price* +this.count[index].num;
				}
				if(type == 'low') {
					if(this.count[index].num == 1) {
						return false;
					}
					this.count[index].num--;
					this.count[index].price = +this.count[index].selectAttr.price* +this.count[index].num;
				}
				this.countPrice();
			},
			// 选中商品
			submitGoods() {
				let list = JSON.stringify(this.count)
				localStorage.setItem('handle', list);
				let stock = this.selectAttr.stock;
				for(let item of this.count) {
					if(item.goods_attr_id == this.selectAttr.id) {
						stock -= item.num
					}
				}
				console.log('stock=>' + stock)
				if(this.number > stock) {
					this.$message.error('超出库存数量('+this.selectAttr.stock+'件)');
					return false
				}
				console.log(this.number)
				if(this.number > 0) {
					for(let item of this.count) {
						if(item.goods_attr_id == this.selectAttr.id) {
							item.num += +this.number
							item.price = +this.selectAttr.price * item.num
							this.goods = null;
							this.countPrice();
							return
						}
					}
					let price = +this.selectAttr.price * this.number;
					let para = {
						id: this.selectAttr.goods_id,
						attr: this.selectAttr.attr_list,
						selectAttr: this.selectAttr,
						num: this.number,
						name: this.goods.name,
						goods_attr_id: this.selectAttr.id,
						cart_id: 0,
						cover_pic: this.selectAttr.pic_url ? this.selectAttr.pic_url : this.goods.cover_pic,
						price: price
					}
					this.count.push(para);
					this.goods = null;
					this.countPrice();
				}else {
					this.$message.error('请输入数量');
				}
			},
			// 计算折扣并缓存
			countPrice() {
				this.totalCountNumber = 0;
				for(let item of this.count) {
					this.totalCountNumber += +item.num
				}
				let list = JSON.stringify(this.count)
				localStorage.setItem('list',list);
				for(let item of this.count) {
					if(item.id == this.temp.id) {
						this.addPrice = +item.price
					}
				}
				this.previewOrder();
				if(this.count.length == 0) {
					this.totalPrice = '0.00';
					this.salePrice = '0.00';
					this.changePrice = false;
					this.showHung = false;
					this.hungOrder = [];
					this.addMoney = false;
					this.use_coupon = {
						id: null
					};
					this.temp_use_coupon = {
						id: null
					}
					this.useIntegral = false;
					this.tempUseIntegral = false;
					this.orderInfo = {
	 					integral: {
							use_num: 0
						}
					}
					this.changeMoney = 0;
					this.step = 0;
					this.mode = 'search';
					this.getMoney = 0;
					this.barcode = '';
				}
			},
			// 修改商品数量
			handleGoods(type) {
				if(type == 'add') {
					if(this.number == this.selectAttr.stock) {
						return false;
					}
					this.number++;
				}
				if(type == 'low') {
					if(this.number == 1) {
						return false;
					}
					this.number--;
				}
			},
			// 选择规格
			chooseAttr(attr,idx,index) {
				for(let i in this.goods.attr_groups[idx].attr_list) {
					this.goods.attr_groups[idx].attr_list[i].active = i == index ? true : false;
				}
				if(idx == this.goods.attr_groups.length - 1) {
					this.$forceUpdate();
					let attr_list = [];
					for(let list of this.goods.attr_groups) {
						for(let attrItem of list.attr_list) {
							if(attrItem.active) {
								let para = {
									attr_group_name: list.attr_group_name,
									attr_group_id: list.attr_group_id,
									attr_id: attrItem.attr_id,
									attr_name: attrItem.attr_name,
								}
								attr_list.push(para)
							}
						}
					}
					setTimeout(()=>{
						for(let item of this.goods.attr) {
							if(JSON.stringify(attr_list) == JSON.stringify(item.attr_list)) {
								this.selectAttr = item;
							}
						}
					})
				}else {
					for(let i in this.goods.attr_groups[this.goods.attr_groups.length - 1].attr_list) {
						this.goods.attr_groups[this.goods.attr_groups.length - 1].attr_list[i].active = false;
					}
					this.attrTraversal('try');
				}
			},
			// 提交售后
			toRefund() {
				if(+this.refundPrice < 0 || +this.refundPrice > +this.refundDetail.total_price) {
					this.$message.error('输入的退款金额有误');
					return false;
				}
				if(!this.refundRemark) {
					this.$message.error('请输入退款说明');
					return false;
				}
				this.refundLoading = true;
				request({
					params: {
						r: 'plugin/teller/web/order/refund-submit',
					},
					data: {
						order_detail_id: this.refundDetail.id,
						type: this.refundType,
						remark: this.refundRemark,
						refund_price: this.refundPrice,
					},
					method: 'post',
				}).then(e => {
					this.refundLoading = false;
					if (e.data.code === 0) {
						this.successMsg = '退款成功';
						this.failVisible = false;
						this.successVisible = true;
						setTimeout(()=>{
							if(this.successVisible) {
								this.closeSuccess();
							}
						},5000)
					} else {
						this.$message.error(e.data.msg);
					}
				})
			},
			// 显示详情
			toDetail(item) {
				let self = this;
				if(item.stock == 0) {
					return false;
				}
				self.loading =  true;
				request({
					params: {
						r: 'plugin/teller/web/goods/detail',
						goods_id: item.id
					},
					method: 'get',
				}).then(e => {
					self.loading =  false;
					if (e.data.code === 0) {
						self.goods = e.data.data.goods;
						let hungOrder = JSON.parse(localStorage.getItem('hung'));
						if(hungOrder && hungOrder.length > 0) {
							for(let item of hungOrder) {
								for(let goods of item.list) {
									for(let attr of self.goods.attr) {
										if(goods.goods_attr_id == attr.id) {
											attr.stock = +attr.stock - +goods.num
										}
									}
								}
							}
						}
						self.number = 1;
						self.selectAttr = self.goods.attr[0];
						for(let i = 0; i < self.goods.attr_groups.length - 1; i++) {
							for(let index in  self.goods.attr_groups[i].attr_list) {
								self.goods.attr_groups[i].attr_list[index].active = index == 0 ? true : false
							}
						}
						self.attrTraversal('first');
					} else {
						self.$message.error(e.data.msg);
					}
				}).catch(e => {
					console.log(e);
				});
			},
			// 检查商品规格
			attrTraversal(type) {
				let list = [];
				let isStock = false;
				if(this.goods.attr_groups.length > 1) {
					for(let i = 0; i < this.goods.attr_groups.length - 1; i++) {
						for(let attr of this.goods.attr_groups[i].attr_list) {
							if(attr.active) {
								let para = {
									attr_group_name: this.goods.attr_groups[i].attr_group_name,
									attr_group_id: this.goods.attr_groups[i].attr_group_id,
									attr_id: attr.attr_id,
									attr_name: attr.attr_name,
								}
								list.push(para);
							}
						}
					}
					for(let item of this.goods.attr) {
						let chooseAttr = JSON.parse(JSON.stringify(item.attr_list));
						chooseAttr.pop();
						setTimeout(()=>{
							if(JSON.stringify(list) == JSON.stringify(chooseAttr)) {
								for(let attr of this.goods.attr_groups[this.goods.attr_groups.length - 1].attr_list) {
									if(attr.attr_id == item.attr_list[this.goods.attr_groups.length - 1].attr_id && attr.attr_name == item.attr_list[this.goods.attr_groups.length - 1].attr_name) {
										attr.stock = item.stock;
										if(type) {
											if(!isStock && attr.stock > 0) {
												isStock = true;
												attr.active = true;
												this.selectAttr = item;
											}else {
												attr.active = false;
											}
										}
										this.$forceUpdate();
									}
								}
							}
						})
					}
				}else {
					for(let index in this.goods.attr_groups[0].attr_list) {
						if(this.goods.attr_groups[0].attr_list[index].attr_id == this.goods.attr[index].attr_list[0].attr_id && this.goods.attr_groups[0].attr_list[index].attr_name == this.goods.attr[index].attr_list[0].attr_name) {
							this.goods.attr_groups[0].attr_list[index].stock = this.goods.attr[index].stock;
							if(type) {
								if(!isStock && this.goods.attr_groups[0].attr_list[index].stock > 0) {
									isStock = true;
									this.goods.attr_groups[0].attr_list[index].active = true;
									this.selectAttr = this.goods.attr[index];
								}else {
									this.goods.attr_groups[0].attr_list[index].active = false;
								}
							}
							this.$forceUpdate();
						}
					}
				}
			},
			// 获取配置
			getSetting() {
				let self = this;
				request({
					params: {
						r: 'plugin/teller/web/manage/index',
					},
					method: 'get',
				}).then(e => {
					if (e.data.code === 0) {
						self.cashier = e.data.data.cashier;
						self.mall = e.data.data.mall;
						self.temp = e.data.data.goods;
						self.sales = e.data.data.sales;
						self.setting = e.data.data.setting;
						self.payment_type = self.setting.payment_type[0];
						self.tabList = e.data.data.tab_list;
						self.changeType = (self.setting.is_goods_change_price_type == 1 && self.setting.most_plus) || (self.setting.is_goods_change_price_type == 2 && self.setting.most_plus_percent) ? 1 : 2;
						self.getList(); 
						if(e.data.data.setting.is_full_reduce == 1) {
							self.getFull();
						}
						if(localStorage.getItem('list') && localStorage.getItem('list').length > 0) {
							this.count = JSON.parse(localStorage.getItem('list'));
							this.countPrice();
						}
						if(localStorage.getItem('hung')) {
							let hung = JSON.parse(localStorage.getItem('hung'));
							this.hungLength = hung.length;
						}
					} else {
						self.$message.error(e.data.msg);
						setTimeout(()=>{
							window.location.href="javascript:history.go(-1)";
						},1000)
					}
				}).catch(e => {
					window.location.href="javascript:history.go(-1)";
					console.log(e);
				});
			},
			// 获取满减活动
			getFull() {
				let self = this;
				request({
					params: {
						r: 'plugin/teller/web/goods/full-reduce',
					},
					method: 'get',
				}).then(e => {
					if (e.data.code === 0) {
						if(!Array.isArray(e.data.data.data)) {
							this.fullReduce = e.data.data.data;
						}
					} else {
						self.$message.error(e.data.msg);
					}
				})
			},
			// 获取满减活动商品
			getFullList(page) {
				let self = this;
				let currentPage = page > 0 ? page : 1;
				if(this.stopFullGoods || page == 1) {
					return false;
				}
				if(currentPage == 1) {
					self.loading =  true;
				}
				let para = {
					r: 'plugin/teller/web/goods/full-reduce-goods-list',
					page: currentPage
				}
				if(self.fullKeyword) {
					para.keyword = self.fullKeyword
				}
				if(self.tabList.length > 0 && self.setting.is_tab == 1 && (self.tabIndex || self.tabIndex == 0)) {
					let index = +self.tabIndex+ 1;
					para.cat_id = self.tabList[index].value;
				}
				request({
					params: para,
					method: 'get',
				}).then(e => {
					self.loading =  false;
					if (e.data.code === 0) {
						let list = e.data.data.list;
						let hungOrder = JSON.parse(localStorage.getItem('hung'));
						if(hungOrder && hungOrder.length > 0) {
							for(let goodsList of hungOrder) {
								for(let goods of goodsList.list) {
									for(let item of list) {
										if(goods.id == item.id) {
											item.stock = +item.stock - +goods.num
										}
									}
								}
							}
						}
						if(currentPage == 1) {
							self.fullList = list;
						}else {
							self.fullList = self.fullList.concat(list)
						}
						if(e.data.data.pagination.pageSize == list.length) {
							self.stopFullGoods = false;
							self.fullGoodsPage = currentPage + 1;
						}else if(self.fullList[self.fullList.length - 1].id != list[list.length - 1].id) {
							self.stopFullGoods = true;
						}
					} else {
						self.$message.error(e.data.msg);
					}
				}).catch(e => {
					console.log(e);
				});
			},
			// 搜索订单
			searchOrderList() {
				this.stopOrder = false;
				this.getOrderList();
			},
			// 获取订单
			getOrderList(page) {
				let self = this;
				if(this.stopOrder || page == 1) {
					return false;
				}
				let currentPage = page > 0 ? page : 1;
				if(currentPage == 1) {
					self.loading =  true;
				}
				let para = {
					r: 'plugin/teller/web/order/order-list',
					page: currentPage
				}
				if(self.orderKeyword) {
					para.keyword = self.orderKeyword
				}
				request({
					params: para,
					method: 'get',
				}).then(e => {
					self.loading =  false;
					if (e.data.code === 0) {
						if(currentPage == 1) {
							self.order = e.data.data.list;
						}else if(self.order[self.order.length - 1].order_id != e.data.data.list[e.data.data.list.length - 1].order_id) {
							self.order = self.order.concat(e.data.data.list)
						}
						if(e.data.data.pagination.pageSize == e.data.data.list.length) {
							this.stopOrder = false;
							this.orderPage = currentPage +1;
						}else {
							this.stopOrder = true;
						}
					} else {
						self.$message.error(e.data.msg);
					}
				}).catch(e => {
					console.log(e);
				});
			},
			// 搜索商品
			toSearch() {
				if(this.activeTab == 0) {
					this.tabIndex = null;
					this.stopGoods = false;
					this.getList();
				}
				if(this.activeTab == 3) {
					this.tabIndex = null;
					this.stopFullGoods = false;
					this.getFullList();
				}
			},
			// 获取商品
			getList(page) {
				let self = this;
				let currentPage = page > 0 ? page : 1;
				if(this.stopGoods || page == 1) {
					return false;
				}
				if(currentPage == 1) {
					self.loading =  true;
				}
				let para = {
					r: 'plugin/teller/web/goods/index',
					page: currentPage
				}
				if(self.keyword) {
					para.keyword = self.keyword
				}
				if(self.tabList.length > 0 && self.setting.is_tab == 1 && (self.tabIndex || self.tabIndex == 0)) {
					let index = +self.tabIndex+ 1;
					para.cat_id = self.tabList[index].value;
				}
				request({
					params: para,
					method: 'get',
				}).then(e => {
					self.loading =  false;
					if (e.data.code === 0) {
						let list = e.data.data.list;
						let hungOrder = JSON.parse(localStorage.getItem('hung'));
						if(hungOrder && hungOrder.length > 0) {
							for(let goodsList of hungOrder) {
								for(let goods of goodsList.list) {
									for(let item of list) {
										if(goods.id == item.id) {
											item.stock = +item.stock - +goods.num
										}
									}
								}
							}
						}
						if(currentPage == 1) {
							self.list = list;
							self.goods = null;
						}else if(self.list[self.list.length - 1].id != list[list.length - 1].id) {
							self.list = self.list.concat(list)
						}
						if(e.data.data.pagination.pageSize == list.length) {
							this.stopGoods = false;
							this.goodsPage = currentPage + 1;
						}else {
							this.stopGoods = true;
						}
					} else {
						self.$message.error(e.data.msg);
					}
				}).catch(e => {
					console.log(e);
				});
			},
			// 选择某个分页
			chooseTab(index) {
				this.toggleView();
				if(this.successPay) {
					this.clearOrder();
				}
				if(this.step > 0) {
					this.stepTab = index;
					if(index == 0 || index == 3) {
						this.activeTab = index;
						this.step = 0;
					}else if(index == 2) {
						this.stopOrder = false;
						this.getOrderList();
					}
					return false;
				}
				if(index == 0) {
					this.stopGoods = false;
					this.getList();
				}else if(index == 2) {
					this.stopOrder = false;
					this.getOrderList();
				}else if(index == 3) {
					this.stopFullGoods = false;
					this.getFullList();
				}
				this.toggleView();
				this.orderDetail = null;
				this.refundDetail = null;
				this.addCredit = false;
				this.loginMember = false;
				this.activeTab = index;
				this.goods = null;
				if(index == 1 && !this.member) {
					this.$nextTick(() => {
						if(!this.is_pad) {
							this.$refs.member.$refs.member.focus();
						}
                    });
				}
			},
		},
	});
</script>
