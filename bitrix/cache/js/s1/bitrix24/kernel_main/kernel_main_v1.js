; /* /bitrix/js/main/session.min.js?17455639662394*/
; /* /bitrix/js/main/core/core_fx.js?174556396716888*/
; /* /bitrix/js/main/pageobject/pageobject.min.js?1745563966570*/
; /* /bitrix/js/main/date/main.date.min.js?174556396621981*/
; /* /bitrix/js/main/core/core_date.js?174556396736080*/
; /* /bitrix/js/main/core/core_timer.min.js?17455639674311*/
; /* /bitrix/js/main/dd.min.js?174556396611114*/
; /* /bitrix/js/main/core/core_dd.min.js?17455639672188*/
; /* /bitrix/js/main/core/core_window.js?174556396798766*/
; /* /bitrix/js/main/core/core_tooltip.js?174556396715634*/
; /* /bitrix/js/main/rating_like.min.js?1745563966259*/
; /* /bitrix/js/main/core/core_uf.min.js?174556396711652*/
; /* /bitrix/js/main/core/core_autosave.js?17455639679741*/

; /* Start:"a:4:{s:4:"full";s:45:"/bitrix/js/main/session.min.js?17455639662394";s:6:"source";s:26:"/bitrix/js/main/session.js";s:3:"min";s:30:"/bitrix/js/main/session.min.js";s:3:"map";s:30:"/bitrix/js/main/session.map.js";}"*/
function CBXSession(){var e=this;this.dateInput=new Date;this.dateCheck=new Date;this.dateHit=new Date;this.notifier=null;this.checkInterval=60;this.checkImmediately=false;this.Expand=function(t){this.key=t;BX.ready(function(){BX.bind(document,"keypress",e.OnUserInput);BX.bind(document.body,"mousemove",e.OnUserInput);BX.bind(document.body,"click",e.OnUserInput);setInterval(e.CheckSession,e.checkInterval*1e3)})};this.OnUserInput=function(){var t=new Date;e.dateInput.setTime(t.valueOf());if((t-e.dateHit)/1e3>e.checkInterval+5&&e.checkImmediately===false){e.checkImmediately=true;e.CheckSession()}};this.CheckSession=function(){var t=new Date;if((t-e.dateCheck)/1e3<e.checkInterval-1&&e.checkImmediately===false){return}e.dateCheck.setTime(t.valueOf());if(e.dateInput>e.dateHit){var i={method:"GET",headers:[{name:"X-Bitrix-Csrf-Token",value:BX.bitrix_sessid()}],dataType:"html",url:"/bitrix/tools/public_session.php?k="+e.key,data:"",onsuccess:function(t){e.CheckResult(t)},lsId:"sess_expand",lsTimeout:e.checkInterval-5};BX.ajax(i)}};this.CheckResult=function(t){var i=new Date;e.dateHit.setTime(i.valueOf());e.checkImmediately=false;if(t=="SESSION_EXPIRED"){if(BX.message("SessExpired")){if(!e.notifier){e.notifier=document.body.appendChild(BX.create("DIV",{props:{className:"bx-session-message"},style:{top:"0",backgroundColor:"#FFEB41",border:"1px solid #EDDA3C",width:"630px",fontFamily:"Arial,Helvetica,sans-serif",fontSize:"13px",fontWeight:"bold",textAlign:"center",color:"black",position:"absolute",zIndex:"10000",padding:"10px"},html:'<a class="bx-session-message-close" '+'style="display:block; width:12px; height:12px; background:url(/bitrix/js/main/core/images/close.gif) center no-repeat; float:right;" '+'href="javascript:bxSession.Close()"></a>'+BX.message("SessExpired")}));BX.ZIndexManager.register(e.notifier);BX.ZIndexManager.bringToFront(e.notifier);var s=BX.GetWindowScrollPos();var n=BX.GetWindowInnerSize();e.notifier.style.left=parseInt(s.scrollLeft+n.innerWidth/2-parseInt(e.notifier.clientWidth)/2)+"px";if(BX.browser.IsIE()){e.notifier.style.top=s.scrollTop+"px";BX.bind(window,"scroll",function(){var t=BX.GetWindowScrollPos();e.notifier.style.top=t.scrollTop+"px"})}else{e.notifier.style.position="fixed"}}e.notifier.style.display=""}}};this.Close=function(){this.notifier.style.display="none"}}var bxSession=new CBXSession;
/* End */
;
; /* Start:"a:4:{s:4:"full";s:47:"/bitrix/js/main/core/core_fx.js?174556396716888";s:6:"source";s:31:"/bitrix/js/main/core/core_fx.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
;(function(window){

var defaultOptions = {
	time: 1.0,
	step: 0.05,
	type: 'linear',

	allowFloat: false
}

/*
options: {
	start: start value or {param: value, param: value}
	finish: finish value or {param: value, param: value}
	time: time to transform in seconds
	type: linear|accelerated|decelerated|custom func name
	callback,
	callback_start,
	callback_complete,

	step: time between steps in seconds
	allowFloat: false|true
}
*/
BX.fx = function(options)
{
	this.options = options;

	if (null != this.options.time)
		this.options.originalTime = this.options.time;
	if (null != this.options.step)
		this.options.originalStep = this.options.step;

	if (!this.__checkOptions())
		return false;

	this.__go = BX.delegate(this.go, this);

	this.PARAMS = {};
}

BX.fx.prototype.__checkOptions = function()
{
	if (typeof this.options.start != typeof this.options.finish)
		return false;

	if (null == this.options.time) this.options.time = defaultOptions.time;
	if (null == this.options.step) this.options.step = defaultOptions.step;
	if (null == this.options.type) this.options.type = defaultOptions.type;
	if (null == this.options.allowFloat) this.options.allowFloat = defaultOptions.allowFloat;

	this.options.time *= 1000;
	this.options.step *= 1000;

	if (typeof this.options.start != 'object')
	{
		this.options.start = {_param: this.options.start};
		this.options.finish = {_param: this.options.finish};
	}

	var i;
	for (i in this.options.start)
	{
		if (null == this.options.finish[i])
		{
			this.options.start[i] = null;
			delete this.options.start[i];
		}
	}

	if (!BX.type.isFunction(this.options.type))
	{
		if (BX.type.isFunction(window[this.options.type]))
			this.options.type = window[this.options.type];
		else if (BX.type.isFunction(BX.fx.RULES[this.options.type]))
			this.options.type = BX.fx.RULES[this.options.type];
		else
			this.options.type = BX.fx.RULES[defaultOptions.type];
	}

	return true;
}

BX.fx.prototype.go = function()
{
	var timeCurrent = new Date().valueOf();
	if (timeCurrent < this.PARAMS.timeFinish)
	{
		for (var i in this.PARAMS.current)
		{
			this.PARAMS.current[i][0] = this.options.type.apply(this, [{
				start_value: this.PARAMS.start[i][0],
				finish_value: this.PARAMS.finish[i][0],
				current_value: this.PARAMS.current[i][0],
				current_time: timeCurrent - this.PARAMS.timeStart,
				total_time: this.options.time
			}]);
		}

		this._callback(this.options.callback);

		if (!this.paused)
			this.PARAMS.timer = setTimeout(this.__go, this.options.step);
	}
	else
	{
		this.stop();
	}
}

BX.fx.prototype._callback = function(cb)
{
	var tmp = {};

	cb = cb || this.options.callback;

	for (var i in this.PARAMS.current)
	{
		tmp[i] = (this.options.allowFloat ? this.PARAMS.current[i][0] : Math.round(this.PARAMS.current[i][0])) + this.PARAMS.current[i][1];
	}

	return cb.apply(this, [null != tmp['_param'] ? tmp._param : tmp]);
}

BX.fx.prototype.start = function()
{
	var i,value, unit;

	this.PARAMS.start = {};
	this.PARAMS.current = {};
	this.PARAMS.finish = {};

	for (i in this.options.start)
	{
		value = +this.options.start[i];
		unit = (this.options.start[i]+'').substring((value+'').length);
		this.PARAMS.start[i] = [value, unit];
		this.PARAMS.current[i] = [value, unit];
		this.PARAMS.finish[i] = [+this.options.finish[i], unit];
	}

	this._callback(this.options.callback_start);
	this._callback(this.options.callback);

	this.PARAMS.timeStart = new Date().valueOf();
	this.PARAMS.timeFinish = this.PARAMS.timeStart + this.options.time;
	this.PARAMS.timer = setTimeout(BX.delegate(this.go, this), this.options.step);

	return this;
}

BX.fx.prototype.pause = function()
{
	if (this.paused)
	{
		this.PARAMS.timer = setTimeout(this.__go, this.options.step);
		this.paused = false;
	}
	else
	{
		clearTimeout(this.PARAMS.timer);
		this.paused = true;
	}
}

BX.fx.prototype.stop = function(silent)
{
	silent = !!silent;
	if (this.PARAMS.timer)
		clearTimeout(this.PARAMS.timer);

	if (null != this.options.originalTime)
		this.options.time = this.options.originalTime;
	if (null != this.options.originalStep)
		this.options.step = this.options.originalStep;

	this.PARAMS.current = this.PARAMS.finish;
	if (!silent) {
		this._callback(this.options.callback);
		this._callback(this.options.callback_complete);
	}
}

/*
type rules of animation
 - linear - simple linear animation
 - accelerated
 - decelerated
*/

/*
	params: {
		start_value, finish_value, current_time, total_time
	}
*/
BX.fx.RULES =
{
	linear: function(params)
	{
		return params.start_value + (params.current_time/params.total_time) * (params.finish_value - params.start_value);
	},

	decelerated: function(params)
	{
		return params.start_value + Math.sqrt(params.current_time/params.total_time) * (params.finish_value - params.start_value);
	},

	accelerated: function(params)
	{
		var q = params.current_time/params.total_time;
		return params.start_value + q * q * (params.finish_value - params.start_value);
	}
}

/****************** effects realizaion ************************/

/*
	type = 'fade' || 'scroll' || 'scale' || 'fold'
*/

BX.fx.hide = function(el, type, opts)
{
	el = BX(el);

	if (typeof type == 'object' && null == opts)
	{
		opts = type;
		type = opts.type
	}

	if (!opts) opts = {};

	if (!BX.type.isNotEmptyString(type))
	{
		el.style.display = 'none';
		return;
	}

	var fxOptions = BX.fx.EFFECTS[type](el, opts, 0);
	fxOptions.callback_complete = function () {
		if (opts.hide !== false)
			el.style.display = 'none';

		if (opts.callback_complete)
			opts.callback_complete.apply(this, arguments);
	}

	return (new BX.fx(fxOptions)).start();
}

BX.fx.show = function(el, type, opts)
{
	el = BX(el);

	if (typeof type == 'object' && null == opts)
	{
		opts = type;
		type = opts.type
	}

	if (!opts) opts = {};

	if (!BX.type.isNotEmptyString(type))
	{
		el.style.display = 'block';
		return;
	}

	var fxOptions = BX.fx.EFFECTS[type](el, opts, 1);

	fxOptions.callback_complete = function () {
		if (opts.show !== false)
			el.style.display = 'block';

		if (opts.callback_complete)
			opts.callback_complete.apply(this, arguments);
	}

	return (new BX.fx(fxOptions)).start();
}

BX.fx.EFFECTS = {
	scroll: function(el, opts, action)
	{
		if (!opts.direction) opts.direction = 'vertical';

		var param = opts.direction == 'horizontal' ? 'width' : 'height';

		var val = parseInt(BX.style(el, param));
		if (isNaN(val))
		{
			val = BX.pos(el)[param];
		}

		if (action == 0)
			var start = val, finish = opts.min_height ? parseInt(opts.min_height) : 0;
		else
			var finish = val, start = opts.min_height ? parseInt(opts.min_height) : 0;

		return {
			'start': start,
			'finish': finish,
			'time': opts.time || defaultOptions.time,
			'type': 'linear',
			callback_start: function () {
				if (BX.style(el, 'position') == 'static')
					el.style.position = 'relative';

				el.style.overflow = 'hidden';
				el.style[param] = start + 'px';
				el.style.display = 'block';
			},
			callback: function (val) {el.style[param] = val + 'px';}
		}
	},

	fade: function(el, opts, action)
	{
		var fadeOpts = {
			'time': opts.time || defaultOptions.time,
			'type': action == 0 ? 'decelerated' : 'linear',
			'start': action == 0 ? 1 : 0,
			'finish': action == 0 ? 0 : 1,
			'allowFloat': true
		};

		if (BX.browser.IsIE() && !BX.browser.IsIE9())
		{
			fadeOpts.start *= 100; fadeOpts.finish *= 100; fadeOpts.allowFloat = false;

			fadeOpts.callback_start = function() {
				el.style.display = 'block';
				el.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity=" + fadeOpts.start + ")";
			};

			fadeOpts.callback = function (val) {
				(el.filters['DXImageTransform.Microsoft.alpha']||el.filters.alpha).opacity = val;
			}
		}
		else
		{
			fadeOpts.callback_start = function () {
				el.style.display = 'block';
			}

			fadeOpts.callback = function (val) {
				el.style.opacity = el.style.KhtmlOpacity = el.style.MozOpacity = val;
			};
		}

		return fadeOpts;
	},

	fold: function (el, opts, action) // 'fold' is a combination of two consequential 'scroll' hidings.
	{
		if (action != 0) return;

		var pos = BX.pos(el);
		var coef = pos.height / (pos.width + pos.height);
		var old_opts = {time: opts.time || defaultOptions.time, callback_complete: opts.callback_complete, hide: opts.hide};

		opts.type = 'scroll';
		opts.direction = 'vertical';
		opts.min_height = opts.min_height || 10;
		opts.hide = false;
		opts.time = coef * old_opts.time;
		opts.callback_complete = function()
		{
			el.style.whiteSpace = 'nowrap';

			opts.direction = 'horizontal';
			opts.min_height = null;

			opts.time = old_opts.time - opts.time;
			opts.hide = old_opts.hide;
			opts.callback_complete = old_opts.callback_complete;

			BX.fx.hide(el, opts);
		}

		return BX.fx.EFFECTS.scroll(el, opts, action);
	},

	scale: function (el, opts, action)
	{
		var val = {width: parseInt(BX.style(el, 'width')), height: parseInt(BX.style(el, 'height'))};
		if (isNaN(val.width) || isNaN(val.height))
		{
			var pos = BX.pos(el)
			val = {width: pos.width, height: pos.height};
		}

		if (action == 0)
			var start = val, finish = {width: 0, height: 0};
		else
			var finish = val, start = {width: 0, height: 0};

		return {
			'start': start,
			'finish': finish,
			'time': opts.time || defaultOptions.time,
			'type': 'linear',
			callback_start: function () {
				el.style.position = 'relative';
				el.style.overflow = 'hidden';
				el.style.display = 'block';
				el.style.height = start.height + 'px';
				el.style.width = start.width + 'px';
			},
			callback: function (val) {
				el.style.height = val.height + 'px';
				el.style.width = val.width + 'px';
			}
		}
	}
}

// Color animation
//
// Set animation rule
// BX.fx.colorAnimate.addRule('animationRule1',"#FFF","#faeeb4", "background-color", 100, 1, true);
// BX.fx.colorAnimate.addRule('animationRule2',"#fc8282","#ff0000", "color", 100, 1, true);
// Params: 1 - animation name, 2 - start color, 3 - end color, 4 - count step, 5 - delay each step, 6 - return color on end animation
//
// Animate color for element
// BX.fx.colorAnimate(BX('element'), 'animationRule1,animationRule2');

var defaultOptionsColorAnimation = {
	arStack: {},
	arRules: {},
	globalAnimationId: 0
}

BX.fx.colorAnimate = function(element, rule, back)
{
	if (element == null)
		return;

	animationId = element.getAttribute('data-animation-id');
	if (animationId == null)
	{
		animationId = defaultOptionsColorAnimation.globalAnimationId;
		element.setAttribute('data-animation-id', defaultOptionsColorAnimation.globalAnimationId++);
	}
	var aRuleList = rule.split(/\s*,\s*/);

	for (var j	= 0; j < aRuleList.length; j++)
	{
		rule = aRuleList[j];

		if (!defaultOptionsColorAnimation.arRules[rule]) continue;

		var i=0;

		if (!defaultOptionsColorAnimation.arStack[animationId])
		{
			defaultOptionsColorAnimation.arStack[animationId] = {};
		}
		else if (defaultOptionsColorAnimation.arStack[animationId][rule])
		{
			i = defaultOptionsColorAnimation.arStack[animationId][rule].i;
			clearInterval(defaultOptionsColorAnimation.arStack[animationId][rule].tId);
		}

		if ((i==0 && back) || (i==defaultOptionsColorAnimation.arRules[rule][3] && !back)) continue;

		defaultOptionsColorAnimation.arStack[animationId][rule] = {'i':i, 'element': element, 'tId':setInterval('BX.fx.colorAnimate.run("'+animationId+'","'+rule+'")', defaultOptionsColorAnimation.arRules[rule][4]),'back':Boolean(back)};
	}
}

BX.fx.colorAnimate.addRule = function (rule, startColor, finishColor, cssProp, step, delay, back)
{
	defaultOptionsColorAnimation.arRules[rule] = [
		BX.util.hex2rgb(startColor),
		BX.util.hex2rgb(finishColor),
		cssProp.replace(/\-(.)/g,function(){return arguments[1].toUpperCase();}),
		step,
		delay || 1,
		back || false
	];
};

BX.fx.colorAnimate.run = function(animationId, rule)
{
	element = defaultOptionsColorAnimation.arStack[animationId][rule].element;

    defaultOptionsColorAnimation.arStack[animationId][rule].i += defaultOptionsColorAnimation.arStack[animationId][rule].back?-1:1;
 	var finishPercent = defaultOptionsColorAnimation.arStack[animationId][rule].i/defaultOptionsColorAnimation.arRules[rule][3];
	var startPercent = 1 - finishPercent;

	var aRGBStart = defaultOptionsColorAnimation.arRules[rule][0];
	var aRGBFinish = defaultOptionsColorAnimation.arRules[rule][1];

	element.style[defaultOptionsColorAnimation.arRules[rule][2]] = 'rgb('+
	Math.floor( aRGBStart['r'] * startPercent + aRGBFinish['r'] * finishPercent ) + ','+
	Math.floor( aRGBStart['g'] * startPercent + aRGBFinish['g'] * finishPercent ) + ','+
	Math.floor( aRGBStart['b'] * startPercent + aRGBFinish['b'] * finishPercent ) +')';

	if ( defaultOptionsColorAnimation.arStack[animationId][rule].i == defaultOptionsColorAnimation.arRules[rule][3] || defaultOptionsColorAnimation.arStack[animationId][rule].i ==0)
	{
		clearInterval(defaultOptionsColorAnimation.arStack[animationId][rule].tId);
		if (defaultOptionsColorAnimation.arRules[rule][5])
			BX.fx.colorAnimate(defaultOptionsColorAnimation.arStack[animationId][rule].element, rule, true);
	}
}


/*
options = {
	delay: 100,
	duration : 3000,
	start : { scroll : document.body.scrollTop, left : 0, opacity :  100 },
	finish : { scroll : document.body.scrollHeight, left : 500, opacity : 10 },
	transition : BitrixAnimation.makeEaseOut(BitrixAnimation.transitions.quart),

	step : function(state)
	{
		document.body.scrollTop = state.scroll;
		button.style.left =  state.left + "px";
		button.style.opacity =  state.opacity / 100;
	},
	complete : function()
	{
		button.style.background = "green";
	}
}

options =
{
	delay : 20,
	duration : 4000,
	transition : BXAnimation.makeEaseOut(BXAnimation.transitions.quart),
	progress : function(progress)
	{
		document.body.scrollTop = Math.round(topMax * progress);
		button.style.left =  Math.round(leftMax * progress) + "px";
		button.style.opacity =  (100 + Math.round((opacityMin - 100) * progress)) / 100;

	},
	complete : function()
	{
		button.style.background = "green";
	}
}
*/

BX.easing = function(options)
{
	this.options = options;
	this.timer = null;
};

BX.easing.prototype.animate = function()
{
	if (!this.options || !this.options.start || !this.options.finish ||
		typeof(this.options.start) != "object" || typeof(this.options.finish) != "object"
		)
		return null;

	for (var propName in this.options.start)
	{
		if (typeof(this.options.finish[propName]) == "undefined")
		{
			delete this.options.start[propName];
		}
	}

	this.options.progress = function(progress) {
		var state = {};
		for (var propName in this.start)
			state[propName] = Math.round(this.start[propName] + (this.finish[propName] - this.start[propName]) * progress);

		if (this.step)
		{
			this.step(state);
		}
	};

	this.animateProgress();
};

BX.easing.prototype.stop = function(completed)
{
	if (this.timer)
	{
		cancelAnimationFrame(this.timer);
		this.timer = null;
		if (completed)
		{
			this.options.complete && this.options.complete();
		}
	}
};

BX.easing.prototype.animateProgress = function()
{
	if (!window.requestAnimationFrame)
	{
		//For old browsers we skip animation
		this.options.progress(1);
		this.options.complete && this.options.complete();
		return;
	}

	var start = null;
	var delta = this.options.transition || BX.easing.transitions.linear;
	var duration = this.options.duration || 1000;
	var animation = BX.proxy(function(time) {

		if (start === null)
		{
			start = time;
		}

		var progress = (time - start) / duration;
		if (progress > 1)
		{
			progress = 1;
		}

		this.options.progress(delta(progress));

		if (progress == 1)
		{
			this.stop(true);
		}
		else
		{
			this.timer = requestAnimationFrame(animation);
		}

	}, this);

	this.timer = requestAnimationFrame(animation);
};

BX.easing.makeEaseInOut = function(delta)
{
	return function(progress) {
		if (progress < 0.5)
			return delta( 2 * progress ) / 2;
		else
			return (2 - delta( 2 * (1-progress) ) ) / 2;
	}
};

BX.easing.makeEaseOut = function(delta)
{
	return function(progress) {
		return 1 - delta(1 - progress);
	};
};

BX.easing.transitions = {

	linear : function(progress)
	{
		return progress;
	},

	quad : function(progress)
	{
		return Math.pow(progress, 2);
	},

	cubic : function(progress) {
		return Math.pow(progress, 3);
	},

	quart : function(progress)
	{
		return Math.pow(progress, 4);
	},

	quint : function(progress)
	{
		return Math.pow(progress, 5);
	},

	circ : function(progress)
	{
		return 1 - Math.sin(Math.acos(progress));
	},

	back : function(progress)
	{
		return Math.pow(progress, 2) * ((1.5 + 1) * progress - 1.5);
	},

	elastic: function(progress)
	{
		return Math.pow(2, 10 * (progress-1)) * Math.cos(20 * Math.PI * 1.5/3 * progress);
	},

	bounce : function(progress)
	{
		for(var a = 0, b = 1; 1; a += b, b /= 2) {
			if (progress >= (7 - 4 * a) / 11) {
				return -Math.pow((11 - 6 * a - 11 * progress) / 4, 2) + Math.pow(b, 2);
			}
		}
	}};


})(window);


/* End */
;
; /* Start:"a:4:{s:4:"full";s:58:"/bitrix/js/main/pageobject/pageobject.min.js?1745563966570";s:6:"source";s:40:"/bitrix/js/main/pageobject/pageobject.js";s:3:"min";s:44:"/bitrix/js/main/pageobject/pageobject.min.js";s:3:"map";s:44:"/bitrix/js/main/pageobject/pageobject.map.js";}"*/
window.BX=BX||{};BX.PageObject={getRootWindow:function(){return BX.PageObject.getTopWindowOfCurrentHost(window)},isCrossOriginObject:function(t){try{void t.location.host}catch(t){return true}return false},getTopWindowOfCurrentHost:function(t){if(!BX.PageObject.isCrossOriginObject(t.parent)&&t.parent!==t&&t.parent.location.host===t.location.host){return BX.PageObject.getTopWindowOfCurrentHost(t.parent)}return t},getParentWindowOfCurrentHost:function(t){if(BX.PageObject.isCrossOriginObject(t.parent)){return t}return t.parent}};
/* End */
;
; /* Start:"a:4:{s:4:"full";s:53:"/bitrix/js/main/date/main.date.min.js?174556396621981";s:6:"source";s:33:"/bitrix/js/main/date/main.date.js";s:3:"min";s:37:"/bitrix/js/main/date/main.date.min.js";s:3:"map";s:37:"/bitrix/js/main/date/main.date.map.js";}"*/
this.BX=this.BX||{};(function(e,t){"use strict";function r(e){if(!t.Type.isStringFilled(e)){return""}return e.replace("YYYY","Y").replace("MMMM","F").replace("MM","m").replace("M","M").replace("DD","d").replace("G","g").replace(/GG/i,"G").replace("H","h").replace(/HH/i,"H").replace("MI","i").replace("SS","s").replace("TT","A").replace("T","a")}const s=new t.Cache.MemoryCache;function n(e){return s.remember(`main.date.format.${e}`,(()=>{let s=t.Extension.getSettings("main.date").get(`formats.${e}`);if(t.Type.isStringFilled(s)&&(e==="FORMAT_DATE"||e==="FORMAT_DATETIME")){s=r(s)}return s}))}let _=function(){function e(){babelHelpers.classCallCheck(this,e)}babelHelpers.createClass(e,null,[{key:"isAmPmMode",value:function e(t){if(t===true){return this._getMessage("AMPM_MODE")}return this._getMessage("AMPM_MODE")!==false}},{key:"convertToUTC",value:function e(r){if(!t.Type.isDate(r)){return null}return new Date(Date.UTC(r.getFullYear(),r.getMonth(),r.getDate(),r.getHours(),r.getMinutes(),r.getSeconds(),r.getMilliseconds()))}},{key:"getNewDate",value:function e(t){return new Date(this.getBrowserTimestamp(t))}},{key:"getBrowserTimestamp",value:function e(t){t=parseInt(t,10);const r=new Date(t*1e3).getTimezoneOffset()*60;return(parseInt(t,10)+parseInt(this._getMessage("SERVER_TZ_OFFSET"))+r)*1e3}},{key:"getServerTimestamp",value:function e(t){t=parseInt(t,10);const r=new Date(t).getTimezoneOffset()*60;return Math.round(t/1e3-(parseInt(this._getMessage("SERVER_TZ_OFFSET"),10)+parseInt(r,10)))}},{key:"formatLastActivityDate",value:function e(t,r,s){const n=this.isAmPmMode(true);const _=n===this.AM_PM_MODE.LOWER?"g:i a":n===this.AM_PM_MODE.UPPER?"g:i A":"H:i";const i=[["tomorrow","#01#"+_],["now","#02#"],["todayFuture","#03#"+_],["yesterday","#04#"+_],["-",this.convertBitrixFormat(this._getMessage("FORMAT_DATETIME")).replace(/:s/g,"")],["s60","sago"],["i60","iago"],["H5","Hago"],["H24","#03#"+_],["d31","dago"],["m12>1","mago"],["m12>0","dago"],["","#05#"]];let a=this.format(i,t,r,s);let o=null;if((o=/^#(\d+)#(.*)/.exec(a))!=null){switch(o[1]){case"01":a=this._getMessage("FD_LAST_SEEN_TOMORROW").replace("#TIME#",o[2]);break;case"02":a=this._getMessage("FD_LAST_SEEN_NOW");break;case"03":a=this._getMessage("FD_LAST_SEEN_TODAY").replace("#TIME#",o[2]);break;case"04":a=this._getMessage("FD_LAST_SEEN_YESTERDAY").replace("#TIME#",o[2]);break;case"05":a=this._getMessage("FD_LAST_SEEN_MORE_YEAR");break;default:a=o[2];break}}return a}},{key:"_getMessage",value:function e(r){return t.Loc.getMessage(r)}},{key:"parse",value:function e(r,s,n,_){if(t.Type.isStringFilled(r)){if(!n){n=this._getMessage("FORMAT_DATE")}if(!_){_=this._getMessage("FORMAT_DATETIME")}let e="";for(let t=1;t<=12;t++){e=e+"|"+this._getMessage("MON_"+t)}const t=new RegExp("([0-9]+|[a-z]+"+e+")","ig");const i=r.match(t);let a=n.match(/(DD|MI|MMMM|MM|M|YYYY)/gi);const o=[];const D=[];const u={};if(!i){return null}if(i.length>a.length){a=_.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/gi)}for(let e=0,t=i.length;e<t;e++){if(i[e].trim()!==""){o[o.length]=i[e]}}for(let e=0,t=a.length;e<t;e++){if(a[e].trim()!==""){D[D.length]=a[e]}}let l=D.findIndex((e=>e==="MMMM"));if(l>0){o[l]=this.getMonthIndex(o[l]);D[l]="MM"}else{l=D.findIndex((e=>e==="M"));if(l>0){o[l]=this.getMonthIndex(o[l]);D[l]="MM"}}for(let e=0,t=D.length;e<t;e++){const t=D[e].toUpperCase();u[t]=t==="T"||t==="TT"?o[e]:parseInt(o[e],10)}if(u["DD"]>0&&u["MM"]>0&&u["YYYY"]>0){const e=new Date;if(s){e.setUTCDate(1);e.setUTCFullYear(u["YYYY"]);e.setUTCMonth(u["MM"]-1);e.setUTCDate(u["DD"]);e.setUTCHours(0,0,0,0)}else{e.setDate(1);e.setFullYear(u["YYYY"]);e.setMonth(u["MM"]-1);e.setDate(u["DD"]);e.setHours(0,0,0,0)}if((!isNaN(u["HH"])||!isNaN(u["GG"])||!isNaN(u["H"])||!isNaN(u["G"]))&&!isNaN(u["MI"])){if(!isNaN(u["H"])||!isNaN(u["G"])){const e=(u["T"]||u["TT"]||"am").toUpperCase()==="PM",t=parseInt(u["H"]||u["G"]||0,10);if(e){u["HH"]=t+(t===12?0:12)}else{u["HH"]=t<12?t:0}}else{u["HH"]=parseInt(u["HH"]||u["GG"]||0,10)}if(isNaN(u["SS"])){u["SS"]=0}if(s){e.setUTCHours(u["HH"],u["MI"],u["SS"])}else{e.setHours(u["HH"],u["MI"],u["SS"])}}return e}}return null}},{key:"getMonthIndex",value:function e(t){const r=t.toUpperCase();const s=["jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec"];const n=["january","february","march","april","may","june","july","august","september","october","november","december"];for(let e=1;e<=12;e++){if(r===this._getMessage("MON_"+e).toUpperCase()||r===this._getMessage("MONTH_"+e).toUpperCase()||r===this._getMessage("MONTH_"+e+"_S").toUpperCase()||r===s[e-1].toUpperCase()||r===n[e-1].toUpperCase()){return e}}return t}},{key:"format",value:function e(r,s,n,_){const i=t.Type.isDate(s)?new Date(s.getTime()):t.Type.isNumber(s)?new Date(s*1e3):new Date;const a=t.Type.isDate(n)?new Date(n.getTime()):t.Type.isNumber(n)?new Date(n*1e3):new Date;const o=!!_;const D=this;if(t.Type.isArray(r)){return f(r,i,a,o)}else{if(!t.Type.isStringFilled(r)){return""}}const u=(r.match(/{{([^{}]*)}}/g)||[]).map((e=>(e.match(/[^{}]+/)||[""])[0]));if(u.length>0){u.forEach(((e,t)=>{r=r.replace("{{"+e+"}}","{{"+t+"}}")}))}const l=/\\?(sago|iago|isago|Hago|dago|mago|Yago|sdiff|idiff|Hdiff|ddiff|mdiff|Ydiff|sshort|ishort|Hshort|dshort|mshort|Yshort|yesterday|today|tommorow|tomorrow|.)/gi;const c={d:()=>T(i).toString().padStart(2,"0"),D:()=>this._getMessage("DOW_"+S(i)),j:()=>T(i),l:()=>this._getMessage("DAY_OF_WEEK_"+S(i)),N:()=>S(i)||7,S:()=>{if(T(i)%10==1&&T(i)!=11){return"st"}else if(T(i)%10==2&&T(i)!=12){return"nd"}else if(T(i)%10==3&&T(i)!=13){return"rd"}else{return"th"}},w:()=>S(i),z:()=>{const e=new Date(g(i),0,1);const t=new Date(g(i),F(i),T(i));return Math.ceil((t-e)/(24*3600*1e3))},W:()=>{const e=new Date(i.getTime());const t=(S(i)+6)%7;d(e,T(e)-t+3);const r=e.getTime();A(e,0,1);if(S(e)!=4){A(e,0,1+(4-S(e)+7)%7)}const s=1+Math.ceil((r-e)/(7*24*3600*1e3));return s.toString().padStart(2,"0")},F:()=>this._getMessage("MONTH_"+(F(i)+1)+"_S"),f:()=>this._getMessage("MONTH_"+(F(i)+1)),m:()=>(F(i)+1).toString().padStart(2,"0"),M:()=>this._getMessage("MON_"+(F(i)+1)),n:()=>F(i)+1,t:()=>{const e=o?new Date(Date.UTC(g(i),F(i)+1,0)):new Date(g(i),F(i)+1,0);return T(e)},L:()=>{const e=g(i);return e%4==0&&e%100!=0||e%400==0?1:0},o:()=>{const e=new Date(i.getTime());d(e,T(e)-(S(i)+6)%7+3);return g(e)},Y:()=>g(i),y:()=>g(i).toString().slice(2),a:()=>h(i)>11?"pm":"am",A:()=>h(i)>11?"PM":"AM",B:()=>{const e=(i.getUTCHours()+1)%24+i.getUTCMinutes()/60+i.getUTCSeconds()/3600;return Math.floor(e*1e3/24).toString().padStart(3,"0")},g:()=>h(i)%12||12,G:()=>h(i),h:()=>(h(i)%12||12).toString().padStart(2,"0"),H:()=>h(i).toString().padStart(2,"0"),i:()=>E(i).toString().padStart(2,"0"),s:()=>m(i).toString().padStart(2,"0"),u:()=>(R(i)*1e3).toString().padStart(6,"0"),e:()=>{if(o){return"UTC"}return""},I:()=>{if(o){return 0}const e=new Date(g(i),0,1);const t=Date.UTC(g(i),0,1);const r=new Date(g(i),6,0);const s=Date.UTC(g(i),6,0);return 0+(e-t!==r-s)},O:()=>{if(o){return"+0000"}const e=i.getTimezoneOffset();const t=Math.abs(e);return(e>0?"-":"+")+(Math.floor(t/60)*100+t%60).toString().padStart(4,"0")},P:function(){if(o){return"+00:00"}const e=this.O();return e.substr(0,3)+":"+e.substr(3)},Z:()=>{if(o){return 0}return-i.getTimezoneOffset()*60},c:()=>"Y-m-d\\TH:i:sP".replace(l,p),r:()=>"D, d M Y H:i:s O".replace(l,p),U:()=>Math.floor(i.getTime()/1e3),sago:()=>H(U((a-i)/1e3),{0:"FD_SECOND_AGO_0",1:"FD_SECOND_AGO_1","10_20":"FD_SECOND_AGO_10_20",MOD_1:"FD_SECOND_AGO_MOD_1",MOD_2_4:"FD_SECOND_AGO_MOD_2_4",MOD_OTHER:"FD_SECOND_AGO_MOD_OTHER"}),sdiff:()=>H(U((a-i)/1e3),{0:"FD_SECOND_DIFF_0",1:"FD_SECOND_DIFF_1","10_20":"FD_SECOND_DIFF_10_20",MOD_1:"FD_SECOND_DIFF_MOD_1",MOD_2_4:"FD_SECOND_DIFF_MOD_2_4",MOD_OTHER:"FD_SECOND_DIFF_MOD_OTHER"}),sshort:()=>this._getMessage("FD_SECOND_SHORT").replace(/#VALUE#/g,U((a-i)/1e3)),iago:()=>H(U((a-i)/60/1e3),{0:"FD_MINUTE_AGO_0",1:"FD_MINUTE_AGO_1","10_20":"FD_MINUTE_AGO_10_20",MOD_1:"FD_MINUTE_AGO_MOD_1",MOD_2_4:"FD_MINUTE_AGO_MOD_2_4",MOD_OTHER:"FD_MINUTE_AGO_MOD_OTHER"}),idiff:()=>H(U((a-i)/60/1e3),{0:"FD_MINUTE_DIFF_0",1:"FD_MINUTE_DIFF_1","10_20":"FD_MINUTE_DIFF_10_20",MOD_1:"FD_MINUTE_DIFF_MOD_1",MOD_2_4:"FD_MINUTE_DIFF_MOD_2_4",MOD_OTHER:"FD_MINUTE_DIFF_MOD_OTHER"}),isago:()=>{const e=U((a-i)/60/1e3);let t=H(e,{0:"FD_MINUTE_0",1:"FD_MINUTE_1","10_20":"FD_MINUTE_10_20",MOD_1:"FD_MINUTE_MOD_1",MOD_2_4:"FD_MINUTE_MOD_2_4",MOD_OTHER:"FD_MINUTE_MOD_OTHER"});t+=" ";const r=U((a-i)/1e3)-e*60;t+=H(r,{0:"FD_SECOND_AGO_0",1:"FD_SECOND_AGO_1","10_20":"FD_SECOND_AGO_10_20",MOD_1:"FD_SECOND_AGO_MOD_1",MOD_2_4:"FD_SECOND_AGO_MOD_2_4",MOD_OTHER:"FD_SECOND_AGO_MOD_OTHER"});return t},ishort:()=>this._getMessage("FD_MINUTE_SHORT").replace(/#VALUE#/g,U((a-i)/60/1e3)),Hago:()=>H(U((a-i)/60/60/1e3),{0:"FD_HOUR_AGO_0",1:"FD_HOUR_AGO_1","10_20":"FD_HOUR_AGO_10_20",MOD_1:"FD_HOUR_AGO_MOD_1",MOD_2_4:"FD_HOUR_AGO_MOD_2_4",MOD_OTHER:"FD_HOUR_AGO_MOD_OTHER"}),Hdiff:()=>H(U((a-i)/60/60/1e3),{0:"FD_HOUR_DIFF_0",1:"FD_HOUR_DIFF_1","10_20":"FD_HOUR_DIFF_10_20",MOD_1:"FD_HOUR_DIFF_MOD_1",MOD_2_4:"FD_HOUR_DIFF_MOD_2_4",MOD_OTHER:"FD_HOUR_DIFF_MOD_OTHER"}),Hshort:()=>this._getMessage("FD_HOUR_SHORT").replace(/#VALUE#/g,U((a-i)/60/60/1e3)),yesterday:()=>this._getMessage("FD_YESTERDAY"),today:()=>this._getMessage("FD_TODAY"),tommorow:()=>this._getMessage("FD_TOMORROW"),tomorrow:()=>this._getMessage("FD_TOMORROW"),dago:()=>H(U((a-i)/60/60/24/1e3),{0:"FD_DAY_AGO_0",1:"FD_DAY_AGO_1","10_20":"FD_DAY_AGO_10_20",MOD_1:"FD_DAY_AGO_MOD_1",MOD_2_4:"FD_DAY_AGO_MOD_2_4",MOD_OTHER:"FD_DAY_AGO_MOD_OTHER"}),ddiff:()=>H(U((a-i)/60/60/24/1e3),{0:"FD_DAY_DIFF_0",1:"FD_DAY_DIFF_1","10_20":"FD_DAY_DIFF_10_20",MOD_1:"FD_DAY_DIFF_MOD_1",MOD_2_4:"FD_DAY_DIFF_MOD_2_4",MOD_OTHER:"FD_DAY_DIFF_MOD_OTHER"}),dshort:()=>this._getMessage("FD_DAY_SHORT").replace(/#VALUE#/g,U((a-i)/60/60/24/1e3)),mago:()=>H(U((a-i)/60/60/24/31/1e3),{0:"FD_MONTH_AGO_0",1:"FD_MONTH_AGO_1","10_20":"FD_MONTH_AGO_10_20",MOD_1:"FD_MONTH_AGO_MOD_1",MOD_2_4:"FD_MONTH_AGO_MOD_2_4",MOD_OTHER:"FD_MONTH_AGO_MOD_OTHER"}),mdiff:()=>H(U((a-i)/60/60/24/31/1e3),{0:"FD_MONTH_DIFF_0",1:"FD_MONTH_DIFF_1","10_20":"FD_MONTH_DIFF_10_20",MOD_1:"FD_MONTH_DIFF_MOD_1",MOD_2_4:"FD_MONTH_DIFF_MOD_2_4",MOD_OTHER:"FD_MONTH_DIFF_MOD_OTHER"}),mshort:()=>this._getMessage("FD_MONTH_SHORT").replace(/#VALUE#/g,U((a-i)/60/60/24/31/1e3)),Yago:()=>H(U((a-i)/60/60/24/365/1e3),{0:"FD_YEARS_AGO_0",1:"FD_YEARS_AGO_1","10_20":"FD_YEARS_AGO_10_20",MOD_1:"FD_YEARS_AGO_MOD_1",MOD_2_4:"FD_YEARS_AGO_MOD_2_4",MOD_OTHER:"FD_YEARS_AGO_MOD_OTHER"}),Ydiff:()=>H(U((a-i)/60/60/24/365/1e3),{0:"FD_YEARS_DIFF_0",1:"FD_YEARS_DIFF_1","10_20":"FD_YEARS_DIFF_10_20",MOD_1:"FD_YEARS_DIFF_MOD_1",MOD_2_4:"FD_YEARS_DIFF_MOD_2_4",MOD_OTHER:"FD_YEARS_DIFF_MOD_OTHER"}),Yshort:()=>H(U((a-i)/60/60/24/365/1e3),{0:"FD_YEARS_SHORT_0",1:"FD_YEARS_SHORT_1","10_20":"FD_YEARS_SHORT_10_20",MOD_1:"FD_YEARS_SHORT_MOD_1",MOD_2_4:"FD_YEARS_SHORT_MOD_2_4",MOD_OTHER:"FD_YEARS_SHORT_MOD_OTHER"}),x:()=>{const e=this.isAmPmMode(true);const t=e===this.AM_PM_MODE.LOWER?"g:i a":e===this.AM_PM_MODE.UPPER?"g:i A":"H:i";return this.format([["tomorrow","tomorrow, "+t],["-",this.convertBitrixFormat(this._getMessage("FORMAT_DATETIME")).replace(/:s/g,"")],["s","sago"],["i","iago"],["today","today, "+t],["yesterday","yesterday, "+t],["",this.convertBitrixFormat(this._getMessage("FORMAT_DATETIME")).replace(/:s/g,"")]],i,a,o)},X:()=>{const e=this.isAmPmMode(true);const t=e===this.AM_PM_MODE.LOWER?"g:i a":e===this.AM_PM_MODE.UPPER?"g:i A":"H:i";const r=this.format([["tomorrow","tomorrow"],["-",this.convertBitrixFormat(this._getMessage("FORMAT_DATE"))],["today","today"],["yesterday","yesterday"],["",this.convertBitrixFormat(this._getMessage("FORMAT_DATE"))]],i,a,o);const s=this.format([["tomorrow",t],["today",t],["yesterday",t],["",""]],i,a,o);if(s.length>0){return this._getMessage("FD_DAY_AT_TIME").replace(/#DAY#/g,r).replace(/#TIME#/g,s)}else{return r}},Q:()=>{const e=U((a-i)/60/60/24/1e3);if(e==0){return this._getMessage("FD_DAY_DIFF_1").replace(/#VALUE#/g,1)}else{return this.format([["d","ddiff"],["m","mdiff"],["","Ydiff"]],i,a)}}};let M=false;if(r[0]&&r[0]=="^"){M=true;r=r.substr(1)}let O=r.replace(l,p);if(M){O=O.replace(/\s*00:00:00\s*/g,"").replace(/(\d\d:\d\d)(:00)/g,"$1").replace(/(\s*00:00\s*)(?!:)/g,"")}if(u.length>0){u.forEach((function(e,t){O=O.replace("{{"+t+"}}",e)}))}return O;function f(e,t,r,s){const n=U((r-t)/1e3);for(let _=0;_<e.length;_++){const i=e[_][0];const a=e[_][1];let o=null;if(i=="s"){if(n<60){return D.format(a,t,r,s)}}else if((o=/^s(\d+)\>?(\d+)?/.exec(i))!=null){if(o[1]&&o[2]){if(n<o[1]&&n>o[2]){return D.format(a,t,r,s)}}else if(n<o[1]){return D.format(a,t,r,s)}}else if(i=="i"){if(n<60*60){return D.format(a,t,r,s)}}else if((o=/^i(\d+)\>?(\d+)?/.exec(i))!=null){if(o[1]&&o[2]){if(n<o[1]*60&&n>o[2]*60){return D.format(a,t,r,s)}}else if(n<o[1]*60){return D.format(a,t,r,s)}}else if(i=="H"){if(n<24*60*60){return D.format(a,t,r,s)}}else if((o=/^H(\d+)\>?(\d+)?/.exec(i))!=null){if(o[1]&&o[2]){if(n<o[1]*60*60&&n>o[2]*60*60){return D.format(a,t,r,s)}}else if(n<o[1]*60*60){return D.format(a,t,r,s)}}else if(i=="d"){if(n<31*24*60*60){return D.format(a,t,r,s)}}else if((o=/^d(\d+)\>?(\d+)?/.exec(i))!=null){if(o[1]&&o[2]){if(n<o[1]*24*60*60&&n>o[2]*24*60*60){return D.format(a,t,r,s)}}else if(n<o[1]*24*60*60){return D.format(a,t,r,s)}}else if(i=="m"){if(n<365*24*60*60){return D.format(a,t,r,s)}}else if((o=/^m(\d+)\>?(\d+)?/.exec(i))!=null){if(o[1]&&o[2]){if(n<o[1]*31*24*60*60&&n>o[2]*31*24*60*60){return D.format(a,t,r,s)}}else if(n<o[1]*31*24*60*60){return D.format(a,t,r,s)}}else if(i=="now"){if(t.getTime()==r.getTime()){return D.format(a,t,r,s)}}else if(i=="today"){const e=g(r);const n=F(r);const _=T(r);const i=s?new Date(Date.UTC(e,n,_,0,0,0,0)):new Date(e,n,_,0,0,0,0);const o=s?new Date(Date.UTC(e,n,_+1,0,0,0,0)):new Date(e,n,_+1,0,0,0,0);if(t>=i&&t<o){return D.format(a,t,r,s)}}else if(i=="todayFuture"){const e=g(r);const n=F(r);const _=T(r);const i=r.getTime();const o=s?new Date(Date.UTC(e,n,_+1,0,0,0,0)):new Date(e,n,_+1,0,0,0,0);if(t>=i&&t<o){return D.format(a,t,r,s)}}else if(i=="yesterday"){const e=g(r);const n=F(r);const _=T(r);const i=s?new Date(Date.UTC(e,n,_-1,0,0,0,0)):new Date(e,n,_-1,0,0,0,0);const o=s?new Date(Date.UTC(e,n,_,0,0,0,0)):new Date(e,n,_,0,0,0,0);if(t>=i&&t<o){return D.format(a,t,r,s)}}else if(i=="tommorow"||i=="tomorrow"){const e=g(r);const n=F(r);const _=T(r);const i=s?new Date(Date.UTC(e,n,_+1,0,0,0,0)):new Date(e,n,_+1,0,0,0,0);const o=s?new Date(Date.UTC(e,n,_+2,0,0,0,0)):new Date(e,n,_+2,0,0,0,0);if(t>=i&&t<o){return D.format(a,t,r,s)}}else if(i=="-"){if(n<0){return D.format(a,t,r,s)}}}return e.length>0?D.format(e[e.length-1][1],t,r,s):""}function g(e){return o?e.getUTCFullYear():e.getFullYear()}function T(e){return o?e.getUTCDate():e.getDate()}function F(e){return o?e.getUTCMonth():e.getMonth()}function h(e){return o?e.getUTCHours():e.getHours()}function E(e){return o?e.getUTCMinutes():e.getMinutes()}function m(e){return o?e.getUTCSeconds():e.getSeconds()}function R(e){return o?e.getUTCMilliseconds():e.getMilliseconds()}function S(e){return o?e.getUTCDay():e.getDay()}function d(e,t){return o?e.setUTCDate(t):e.setDate(t)}function A(e,t,r){return o?e.setUTCMonth(t,r):e.setMonth(t,r)}function H(e,t){const r=e<100?Math.abs(e):Math.abs(e%100);const s=r%10;let n="";if(r==0){n=D._getMessage(t["0"])}else if(r==1){n=D._getMessage(t["1"])}else if(r>=10&&r<=20){n=D._getMessage(t["10_20"])}else if(s==1){n=D._getMessage(t["MOD_1"])}else if(2<=s&&s<=4){n=D._getMessage(t["MOD_2_4"])}else{n=D._getMessage(t["MOD_OTHER"])}return n.replace(/#VALUE#/g,e)}function p(e,t){if(c[e]){return c[e]()}else{return t}}function U(e){return e>=0?Math.floor(e):Math.ceil(e)}}}]);return e}();babelHelpers.defineProperty(_,"AM_PM_MODE",{UPPER:1,LOWER:2,NONE:false});babelHelpers.defineProperty(_,"convertBitrixFormat",r);babelHelpers.defineProperty(_,"getFormat",n);function i(e,t){a(e,t);t.add(e)}function a(e,t){if(t.has(e)){throw new TypeError("Cannot initialize the same private elements twice on an object")}}function o(e,t,r){if(!t.has(e)){throw new TypeError("attempted to get private field on non-instance")}return r}const D={format:"Y m d H i s",style:"long"};var u=new WeakSet;var l=new WeakSet;var c=new WeakSet;var M=new WeakSet;var O=new WeakSet;var f=new WeakSet;let g=function(){function e(t){babelHelpers.classCallCheck(this,e);i(this,f);i(this,O);i(this,M);i(this,c);i(this,l);i(this,u);this.milliseconds=Math.abs(t)}babelHelpers.createClass(e,[{key:"format",value:function e(r=D){const s={...D,...r};const n=t.Loc.getMessage("FD_UNIT_ORDER").split(" ");const _=o(this,u,T).call(this,s.style);const i=new Set(s.format.split(" "));const a=o(this,l,F).call(this,s.format);return n.filter((e=>i.has(e))).map((e=>o(this,c,h).call(this,e,e!==a,s.style))).filter((e=>e!=="")).join(_)}},{key:"formatClosest",value:function e(t=D){const r={...D,...t};const s=o(this,l,F).call(this,r.format);return o(this,c,h).call(this,s,false,r.style)}},{key:"seconds",get:function(){return Math.floor(this.milliseconds/e.getUnitDurations().s)}},{key:"minutes",get:function(){return Math.floor(this.milliseconds/e.getUnitDurations().i)}},{key:"hours",get:function(){return Math.floor(this.milliseconds/e.getUnitDurations().H)}},{key:"days",get:function(){return Math.floor(this.milliseconds/e.getUnitDurations().d)}},{key:"months",get:function(){return Math.floor(this.milliseconds/e.getUnitDurations().m)}},{key:"years",get:function(){return Math.floor(this.milliseconds/e.getUnitDurations().Y)}}],[{key:"createFromSeconds",value:function t(r){return new e(r*e.getUnitDurations().s)}},{key:"createFromMinutes",value:function t(r){return new e(r*e.getUnitDurations().i)}},{key:"getUnitDurations",value:function e(){return{s:1e3,i:6e4,H:36e5,d:864e5,m:26784e5,Y:31536e6}}}]);return e}();function T(e){if(e==="short"){return t.Loc.getMessage("FD_SEPARATOR_SHORT").replaceAll("&#32;"," ")}return t.Loc.getMessage("FD_SEPARATOR").replaceAll("&#32;"," ")}function F(e){const t=new Set(e.split(" "));const r=Object.entries(g.getUnitDurations()).filter((([e])=>t.has(e)));return r.reduce(((e,t)=>{const r=Math.floor(this.milliseconds/t[1])>=1;const s=t[1]>e[1];return r&&s?t:e}),r[0])[0]}function h(e,t,r){const s=t?o(this,M,E).call(this,e):o(this,O,m).call(this,e);if(t&&s===0){return""}const n=Date.now()/1e3;const i=s*o(this,f,R).call(this,e)/1e3;const a=r==="short"?`${e}short`:`${e}diff`;return _.format(a,n-i,n)}function E(e){const t={s:this.seconds%60,i:this.minutes%60,H:this.hours%24,d:this.days%31,m:this.months%12,Y:this.years};return t[e]}function m(e){const t={s:this.seconds,i:this.minutes,H:this.hours,d:this.days,m:this.months,Y:this.years};return t[e]}function R(e){return g.getUnitDurations()[e]}const S=new t.Cache.MemoryCache;const d={get SERVER_TO_UTC(){return S.remember("SERVER_TO_UTC",(()=>t.Text.toInteger(t.Loc.getMessage("SERVER_TZ_OFFSET"))))},get USER_TO_SERVER(){return S.remember("USER_TO_SERVER",(()=>t.Text.toInteger(t.Loc.getMessage("USER_TZ_OFFSET"))))},get BROWSER_TO_UTC(){return S.remember("BROWSER_TO_UTC",(()=>{const e=t.Text.toInteger((new Date).getTimezoneOffset()*60);return-e}))}};Object.freeze(d);function A(e){if(t.Type.isDate(e)){return p(e)}return t.Text.toInteger(e)}function H(e){return new Date(e*1e3)}function p(e){return Math.floor(e.getTime()/1e3)}let U=d;let y=null;function C(){return U}function I(){var e;return(e=y)!==null&&e!==void 0?e:p(new Date)}let N=function(){function e(){babelHelpers.classCallCheck(this,e)}babelHelpers.createClass(e,null,[{key:"getDate",value:function e(r=null){const s=t.Type.isNumber(r)?r:this.getTimestamp();return H(s)}},{key:"toUserDate",value:function e(t){return H(this.toUser(t))}},{key:"toServerDate",value:function e(t){return H(this.toServer(t))}},{key:"toUser",value:function e(t){return this.toServer(t)+C().USER_TO_SERVER}},{key:"toServer",value:function e(t){return A(t)-C().BROWSER_TO_UTC+C().SERVER_TO_UTC}},{key:"getTimestamp",value:function e(){return I()}}]);return e}();let Y=function(){function e(){babelHelpers.classCallCheck(this,e)}babelHelpers.createClass(e,null,[{key:"getDate",value:function e(r=null){if(t.Type.isNumber(r)){const e=C().SERVER_TO_UTC-C().BROWSER_TO_UTC;return H(r+e)}return N.toServerDate(N.getDate())}},{key:"toUserDate",value:function e(t){return H(this.toUser(t))}},{key:"toBrowserDate",value:function e(t){return H(this.toBrowser(t))}},{key:"toUser",value:function e(t){return A(t)+C().USER_TO_SERVER}},{key:"toBrowser",value:function e(t){return A(t)+C().BROWSER_TO_UTC-C().SERVER_TO_UTC}},{key:"getTimestamp",value:function e(){return N.toServer(N.getTimestamp())}}]);return e}();function w(e,t,r){b(e,t);v(r,"get");return k(e,r)}function v(e,t){if(e===undefined){throw new TypeError("attempted to "+t+" private static field before its declaration")}}function b(e,t){if(e!==t){throw new TypeError("Private static access of wrong provenance")}}function k(e,t){if(t.get){return t.get.call(e)}return t.value}let G=function(){function e(){babelHelpers.classCallCheck(this,e)}babelHelpers.createClass(e,null,[{key:"getDate",value:function r(s=null){if(t.Type.isNumber(s)){return H(s+w(this,e,B))}return H(this.getTimestamp())}},{key:"toBrowserDate",value:function e(t){return H(this.toBrowser(t))}},{key:"toServerDate",value:function e(t){return H(this.toServer(t))}},{key:"toUTCTimestamp",value:function t(r){return A(r)-w(this,e,B)}},{key:"toBrowser",value:function e(t){return A(t)+C().BROWSER_TO_UTC-C().SERVER_TO_UTC-C().USER_TO_SERVER}},{key:"toServer",value:function e(t){return A(t)-C().USER_TO_SERVER}},{key:"getTimestamp",value:function e(){return N.toUser(N.getTimestamp())}}]);return e}();function P(){const e=C().SERVER_TO_UTC+C().USER_TO_SERVER;return e-C().BROWSER_TO_UTC}var B={get:P,set:void 0};const x=Object.freeze({BrowserTime:N,Offset:d,ServerTime:Y,UserTime:G});e.Timezone=x;e.Date=_;e.DateTimeFormat=_;e.DurationFormat=g})(this.BX.Main=this.BX.Main||{},BX);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:49:"/bitrix/js/main/core/core_date.js?174556396736080";s:6:"source";s:33:"/bitrix/js/main/core/core_date.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
;(function(){

if (BX.date)
	return;

BX.date = BX.Main.Date;

/************************************** calendar class **********************************/

var obCalendarSingleton = null;

/*
params: {
	node: bind element || document.body

	value - start value in site format (using 'field' param if 'value' does not exist)
	callback - date check handler. can return false to prevent calendar closing.
	callback_after - another handler, called after date picking

	field - field to read/write data

	bTime = true - whether to enable time control
	bHideTime = false - whether to hide time control by default

	currentTime - current UTC time()

}
*/


BX.calendar = function(params)
{
	return BX.calendar.get().Show(params);
};

BX.calendar.get = function()
{
	if (!obCalendarSingleton)
		obCalendarSingleton = new BX.JCCalendar();

	return obCalendarSingleton;
};

// simple func for compatibility with the oldies
BX.calendar.InsertDaysBack = function(input, days)
{
	if (days != '')
	{
		var d = new Date();
		if(days > 0)
		{
			d.setTime(d.valueOf() - days*86400000);
		}

		input.value = BX.date.format(BX.date.convertBitrixFormat(BX.message('FORMAT_DATE')), d, null);
	}
	else
	{
		input.value = '';
	}
};

BX.calendar.ValueToString = function(value, bTime, bUTC)
{
	return BX.date.format(
		BX.date.convertBitrixFormat(BX.message(bTime ? 'FORMAT_DATETIME' : 'FORMAT_DATE')),
		value,
		null,
		!!bUTC
	);
};

BX.calendar.ValueToStringFormat = function(value, bitrixFormat, bUTC)
{
	return BX.date.format(
		BX.date.convertBitrixFormat(bitrixFormat),
		value,
		null,
		!!bUTC
	);
};


BX.CalendarPeriod =
{
	Init: function(inputFrom, inputTo, selPeriod)
	{
		if((inputFrom.value != "" || inputTo.value != "") && selPeriod.value == "")
			selPeriod.value = "interval";

		selPeriod.onchange();
	},

	ChangeDirectOpts: function(peroidValue, selPParent) // "week" || "others"
	{
		var selDirect = BX.findChild(selPParent, {'className':'adm-select adm-calendar-direction'}, true);

		if(peroidValue == "week")
		{
			selDirect.options[0].text = BX.message('JSADM_CALEND_PREV_WEEK');
			selDirect.options[1].text = BX.message('JSADM_CALEND_CURR_WEEK');
			selDirect.options[2].text = BX.message('JSADM_CALEND_NEXT_WEEK');
		}
		else
		{
			selDirect.options[0].text = BX.message('JSADM_CALEND_PREV');
			selDirect.options[1].text = BX.message('JSADM_CALEND_CURR');
			selDirect.options[2].text = BX.message('JSADM_CALEND_NEXT');
		}
	},

	SaveAndClearInput: function(oInput)
	{
		if(!window.SavedPeriodValues)
			window.SavedPeriodValues = {};

		window.SavedPeriodValues[oInput.id] = oInput.value;
		oInput.value="";
	},

	RestoreInput: function(oInput)
	{
		if(!window.SavedPeriodValues || !window.SavedPeriodValues[oInput.id])
			return;

		oInput.value = window.SavedPeriodValues[oInput.id];
		delete(window.SavedPeriodValues[oInput.id]);
	},

	OnChangeP: function(sel)
	{
		var selPParent = sel.parentNode.parentNode;
		var bShowFrom, bShowTo, bShowDirect, bShowSeparate;
		bShowFrom = bShowTo = bShowDirect = bShowSeparate = false;

		var inputFromWrap = BX.findChild(selPParent, {'className':'adm-input-wrap adm-calendar-inp adm-calendar-first'});
		var inputToWrap = BX.findChild(selPParent, {'className':'adm-input-wrap adm-calendar-second'});
		var selDirectWrap = BX.findChild(selPParent, {'className':'adm-select-wrap adm-calendar-direction'});
		var separator = BX.findChild(selPParent, {'className':'adm-calendar-separate'});
		var inputFrom = BX.findChild(selPParent, {'className':'adm-input adm-calendar-from'},true);
		var inputTo = BX.findChild(selPParent, {'className':'adm-input adm-calendar-to'},true);

		// define who must be shown
		switch (sel.value)
		{
			case "day":
			case "week":
			case "month":
			case "quarter":
			case "year":
				bShowDirect=true;
				BX.CalendarPeriod.OnChangeD(selDirectWrap.children[0]);
				break;

			case "before":
				bShowTo = true;
				break;

			case "after":
				bShowFrom = true;
				break;

			case "exact":
				bShowFrom= true;
				break;

			case "interval":
				bShowFrom = bShowTo = bShowSeparate = true;
				BX.CalendarPeriod.RestoreInput(inputFrom);
				BX.CalendarPeriod.RestoreInput(inputTo);

				break;

			case "":
				BX.CalendarPeriod.SaveAndClearInput(inputFrom);
				BX.CalendarPeriod.SaveAndClearInput(inputTo);
				break;

			default:
				break;

		}

		BX.CalendarPeriod.ChangeDirectOpts(sel.value, selPParent);

		inputFromWrap.style.display = (bShowFrom? 'inline-block':'none');
		inputToWrap.style.display = (bShowTo? 'inline-block':'none');
		selDirectWrap.style.display = (bShowDirect? 'inline-block':'none');
		separator.style.display = (bShowSeparate? 'inline-block':'none');
	},


	OnChangeD: function(sel)
	{
		var selPParent = sel.parentNode.parentNode;
		var inputFrom = BX.findChild(selPParent, {'className':'adm-input adm-calendar-from'},true);
		var inputTo = BX.findChild(selPParent, {'className':'adm-input adm-calendar-to'},true);
		var selPeriod = BX.findChild(selPParent, {'className':'adm-select adm-calendar-period'},true);

		var offset=0;

		switch (sel.value)
		{
			case "previous":
				offset = -1;
				break;

			case "next":
				offset = 1;
				break;

			case "current":
			default:
				break;

		}

		var from = false;
		var to = false;

		var today = new Date();
		var year = today.getFullYear();
		var month = today.getMonth();
		var day = today.getDate();
		var dayW = today.getDay();

		if (dayW == 0)
				dayW = 7;

		switch (selPeriod.value)
		{
			case "day":
				from = new Date(year, month, day+offset, 0, 0, 0);
				to = new Date(year, month, day+offset, 23, 59, 59);
				break;

			case "week":
				from = new Date(year, month, day-dayW+1+offset*7, 0, 0, 0);
				to = new Date(year, month, day+(7-dayW)+offset*7, 23, 59, 59);
				break;

			case "month":
				from = new Date(year, month+offset, 1, 0, 0, 0);
				to = new Date(year, month+1+offset, 0, 23, 59, 59);
				break;

			case "quarter":
				var quarterNum = Math.floor((month/3))+offset;
				from = new Date(year, 3*(quarterNum), 1, 0, 0, 0);
				to = new Date(year, 3*(quarterNum+1), 0, 23, 59, 59);
				break;

			case "year":
				from = new Date(year+offset, 0, 1, 0, 0, 0);
				to = new Date(year+1+offset, 0, 0, 23, 59, 59);
				break;

			default:
				break;
		}

		var format = window[inputFrom.name+"_bTime"] ? BX.message('FORMAT_DATETIME') : BX.message('FORMAT_DATE');

		if(from)
		{
			inputFrom.value = BX.formatDate(from, format);
			BX.addClass(inputFrom,"adm-calendar-inp-setted");
		}

		if(to)
		{
			inputTo.value = BX.formatDate(to, format);
			BX.addClass(inputTo,"adm-calendar-inp-setted");
		}
	}
};


BX.JCCalendar = function()
{
	this.params = {};

	this.bAmPm = BX.isAmPmMode();

	this.popup = null;
	this.popup_month = null;
	this.popup_year = null;
	this.month_popup_classname = '';
	this.year_popup_classname = '';
	this.value = null;
	this._layers = {};
	this._current_layer = null;

	this.DIV = null;
	this.PARTS = {};

	this.weekStart = 0;
	this.numRows = 6;

	this._create = function(params)
	{
		this.popup = new BX.PopupWindow('calendar_popup_' + Math.random(), params.node, {
			closeByEsc: true,
			autoHide: false,
			content: this._get_content(),
			bindOptions: {forceBindPosition: true}
		});

		BX.bind(this.popup.popupContainer, 'click', function(event) {
			event.stopPropagation();
		});
	};

	this._auto_hide_disable = function()
	{
		BX.unbind(document, 'click', BX.proxy(this._auto_hide, this));
	};

	this._auto_hide_enable = function()
	{
		BX.bind(document, 'click', BX.proxy(this._auto_hide, this));
	};

	this._auto_hide = function(e)
	{
		this._auto_hide_disable();
		this.popup.close();
	};

	this._get_content = function()
	{
		var _layer_onclick = BX.delegate(function(e) {
			e = e||window.event;
			this.SetDate(
				new Date(parseInt(BX.proxy_context.getAttribute('data-date'))),
				(e.type === 'dblclick' || (this.params.bCompatibility && !this.params.bTimeVisibility))
			)
		}, this);

		this.DIV = BX.create('DIV', {
			props: {className: 'bx-calendar'},
			children: [
				BX.create('DIV', {
					props: {
						className: 'bx-calendar-header'
					},
					children: [
						BX.create('A', {
							attrs: {href: 'javascript:void(0)'},
							props: {className: 'bx-calendar-left-arrow'},
							events: {click: BX.proxy(this._prev, this)}
						}),

						BX.create('SPAN', {
							props: {className: 'bx-calendar-header-content'},
							children: [
								(this.PARTS.MONTH = BX.create('A', {
									attrs: {href: 'javascript:void(0)'},
									props: {className: 'bx-calendar-top-month'},
									events: {click: BX.proxy(this._menu_month, this)}
								})),

								(this.PARTS.YEAR = BX.create('A', {
									attrs: {href: 'javascript:void(0)'},
									props: {className: 'bx-calendar-top-year'},
									events: {click: BX.proxy(this._menu_year, this)}
								}))
							]
						}),

						BX.create('A', {
							attrs: {href: 'javascript:void(0)'},
							props: {className: 'bx-calendar-right-arrow'},
							events: {click: BX.proxy(this._next, this)}
						})
					]
				}),

				(this.PARTS.WEEK = BX.create('DIV', {
					props: {
						className: 'bx-calendar-name-day-wrap'
					}
				})),

				(this.PARTS.LAYERS = BX.create('DIV', {
					props: {
						className: 'bx-calendar-cell-block'
					},
					events: {
						click: BX.delegateEvent({className: 'bx-calendar-cell'}, _layer_onclick),
						dblclick: BX.delegateEvent({className: 'bx-calendar-cell'}, _layer_onclick)
					}
				})),

				(this.PARTS.TIME = BX.create('DIV', {
					props: {
						className: 'bx-calendar-set-time-wrap'
					},
					events: {
						click: BX.delegateEvent(
							{attr: 'data-action'},
							BX.delegate(this._time_actions, this)
						)
					},
					html: '<a href="javascript:void(0)" data-action="time_show" class="bx-calendar-set-time"><i></i>'+BX.message('CAL_TIME_SET')+'</a><div class="bx-calendar-form-block"><span class="bx-calendar-form-text">'+BX.message('CAL_TIME')+'</span><span class="bx-calendar-form"><input type="text" class="bx-calendar-form-input" maxwidth="2" onkeyup="BX.calendar.get()._check_time()" /><span class="bx-calendar-form-separator"></span><input type="text" class="bx-calendar-form-input" maxwidth="2" onkeyup="BX.calendar.get()._check_time()" />'+(this.bAmPm?'<span class="bx-calendar-AM-PM-block"><span class="bx-calendar-AM-PM-text" data-action="time_ampm"></span><span class="bx-calendar-form-arrow-r"><a href="javascript:void(0)" class="bx-calendar-form-arrow-top" data-action="time_ampm_up"><i></i></a><a href="javascript:void(0)" class="bx-calendar-form-arrow-bottom" data-action="time_ampm_down"><i></i></a></span></span>':'')+'</span><a href="javascript:void(0)" data-action="time_hide" class="bx-calendar-form-close"><i></i></a></div>'
				})),

				(this.PARTS.BUTTONS = BX.create('DIV', {
					props: {className: 'bx-calendar-button-block'},
					events: {
						click: BX.delegateEvent(
							{attr: 'data-action'},
							BX.delegate(this._button_actions, this)
						)
					},
					html: '<a href="javascript:void(0)" class="bx-calendar-button bx-calendar-button-select" data-action="submit"><span class="bx-calendar-button-left"></span><span class="bx-calendar-button-text">'+BX.message('CAL_BUTTON')+'</span><span class="bx-calendar-button-right"></span></a><a href="javascript:void(0)" class="bx-calendar-button bx-calendar-button-cancel" data-action="cancel"><span class="bx-calendar-button-left"></span><span class="bx-calendar-button-text">'+BX.message('JS_CORE_WINDOW_CLOSE')+'</span><span class="bx-calendar-button-right"></span></a>'
				}))
			]
		});

		this.PARTS.TIME_INPUT_H = BX.findChild(this.PARTS.TIME, {tag: 'INPUT'}, true);
		this.PARTS.TIME_INPUT_M = this.PARTS.TIME_INPUT_H.nextSibling.nextSibling;

		if (this.bAmPm)
			this.PARTS.TIME_AMPM = this.PARTS.TIME_INPUT_M.nextSibling.firstChild;

		var spinner = (new BX.JCSpinner({
			input: this.PARTS.TIME_INPUT_H,
			callback_change: BX.proxy(this._check_time, this),
			bSaveValue: false
		})).Show();
		spinner.className = 'bx-calendar-form-arrow-l';
		this.PARTS.TIME_INPUT_H.parentNode.insertBefore(spinner, this.PARTS.TIME_INPUT_H);

		spinner = (new BX.JCSpinner({
			input: this.PARTS.TIME_INPUT_M,
			callback_change: BX.proxy(this._check_time, this),
			bSaveValue: true
		})).Show();
		spinner.className = 'bx-calendar-form-arrow-r';
		if (!this.PARTS.TIME_INPUT_M.nextSibling)
			this.PARTS.TIME_INPUT_M.parentNode.appendChild(spinner);
		else
			this.PARTS.TIME_INPUT_M.parentNode.insertBefore(spinner, this.PARTS.TIME_INPUT_M.nextSibling);

		for (var i = 0; i < 7; i++)
		{
			this.PARTS.WEEK.appendChild(BX.create('SPAN', {
				props: {
					className: 'bx-calendar-name-day'
				},
				text: BX.message('DOW_' + ((i + this.weekStart) % 7))
			}));
		}

		return this.DIV;
	};

	this._time_actions = function()
	{
		switch (BX.proxy_context.getAttribute('data-action'))
		{
			case 'time_show':
				BX.addClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
				if (this.params.bCompatibility)
				{
					BX.removeClass(this.PARTS.BUTTONS, 'bx-calendar-buttons-disabled');
				}
				this.popup.adjustPosition();
			break;
			case 'time_hide':
				BX.removeClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
				if (this.params.bCompatibility)
				{
					this._saveChoice('hide');
					BX.addClass(this.PARTS.BUTTONS, 'bx-calendar-buttons-disabled');
				}
				this.popup.adjustPosition();
			break;
			case 'time_ampm':
				this.PARTS.TIME_AMPM.innerHTML = this.PARTS.TIME_AMPM.innerHTML == 'AM' ? 'PM' : 'AM';
			break;
			case 'time_ampm_up':
				this._check_time({bSaveValue: false}, null, 12);
				return;
			break;
			case 'time_ampm_down':
				this._check_time({bSaveValue: false}, null, -12);
				return;
			break;
		}

		this._check_time();
	};

	this._button_actions = function()
	{
		switch (BX.proxy_context.getAttribute('data-action'))
		{
			case 'submit':
				if (this.params.bCompatibility)
				{
					this._saveChoice('show');
				}
				this.SaveValue();
			break;
			case 'cancel':
				this.Close();
			break;
		}
	};

	this._saveChoice = function(state)
	{
		if (this.params.bCategoryTimeVisibilityOption)
		{
			BX.userOptions.save(
				this.params.bCategoryTimeVisibilityOption,
				this.params.bNameTimeVisibilityOption,
				'visibility',
				(state === 'show' ? 'Y' : 'N')
			);
		}

		this._bTimeVisibility = (state === 'show');
		this.params.bTimeVisibility = this._bTimeVisibility;
	};

	this._check_time = function(params, value, direction)
	{
		var h = parseInt(this.PARTS.TIME_INPUT_H.value.substring(0,5),10)||0,
			m = parseInt(this.PARTS.TIME_INPUT_M.value.substring(0,5),10)||0,
			bChanged = false;

		if (!!params && !params.bSaveValue)
		{
			this.value.setUTCHours(this.value.getUTCHours() + direction);
		}
		else if (!isNaN(h))
		{
			if (this.bAmPm)
			{
				if (h != 12 && this.PARTS.TIME_AMPM.innerHTML == 'PM')
				{
					h += 12;
				}
			}

			bChanged = true;
			this.value.setUTCHours(h % 24);
		}

		if (!isNaN(m))
		{
			bChanged = true;
			this.value.setUTCMinutes(m % 60);
		}

		if (bChanged)
		{
			this.SetValue(this.value);
		}
	};

	this._set_layer = function()
	{
		var layerId = parseInt(this.value.getUTCFullYear() + '' + BX.util.str_pad_left(this.value.getUTCMonth()+'', 2, "0"));

		if (!this._layers[layerId])
		{
			this._layers[layerId] = this._create_layer();
			this._layers[layerId].BXLAYERID = layerId;
		}

		if (this._current_layer)
		{
			var v = new Date(this.value.valueOf());
			v.setUTCHours(0); v.setUTCMinutes(0);

			var cur_value = BX.findChild(this._layers[layerId], {
					tag: 'A',
					className: 'bx-calendar-active'
				}, true),
				new_value = BX.findChild(this._layers[layerId], {
					tag: 'A',
					attr: {
						'data-date' : v.valueOf() + ''
					}
				}, true);

			if (cur_value)
			{
				BX.removeClass(cur_value, 'bx-calendar-active');
			}

			if (new_value)
			{
				BX.addClass(new_value, 'bx-calendar-active');
			}

			this._replace_layer(this._current_layer, this._layers[layerId]);
		}
		else
		{
			this.PARTS.LAYERS.appendChild(this._layers[layerId]);
		}

		this._current_layer = this._layers[layerId];
	};

	this._replace_layer = function(old_layer, new_layer)
	{
		if (old_layer != new_layer)
		{
			if (!BX.browser.IsIE() || BX.browser.IsDoctype())
			{
				var dir = old_layer.BXLAYERID > new_layer.BXLAYERID ? 1 : -1;

				var old_top = 0;
				var new_top = -dir * old_layer.offsetHeight;

				old_layer.style.position = 'relative';
				old_layer.style.top = "0px";
				old_layer.style.zIndex = 5;

				new_layer.style.position = 'absolute';
				new_layer.style.top = new_top + 'px';
				new_layer.style.zIndex = 6;

				this.PARTS.LAYERS.appendChild(new_layer);

				var delta = 15;

				var f;
				(f = function() {
					new_top += dir * delta;
					old_top += dir * delta;

					if (dir * new_top < 0)
					{
						old_layer.style.top = old_top + 'px';
						new_layer.style.top = new_top + 'px';
						setTimeout(f, 10);
					}
					else
					{
						old_layer.parentNode.removeChild(old_layer);

						new_layer.style.top = "0px";
						new_layer.style.position = 'static';
						new_layer.style.zIndex = 0;
					}
				})();
			}
			else
			{
				this.PARTS.LAYERS.replaceChild(new_layer, old_layer);
			}
		}
	};

	this._create_layer = function()
	{
		var l = BX.create('DIV', {
			props: {
				className: 'bx-calendar-layer'
			}
		});

		var month_start = new Date(this.value);
		month_start.setUTCHours(0);
		month_start.setUTCMinutes(0);

		month_start.setUTCDate(1);

		if (month_start.getUTCDay() != this.weekStart)
		{
			var d = month_start.getUTCDay() - this.weekStart;
			d += d < 0 ? 7 : 0;
			month_start.setUTCDate(month_start.getUTCDate()-d);
		}

		var cur_month = this.value.getUTCMonth(),
			cur_day = this.value.getUTCDate(),
			s = '';
		for (var i = 0; i < this.numRows; i++)
		{
			s += '<div class="bx-calendar-range'
				+(i == this.numRows-1 ? ' bx-calendar-range-noline' : '')
				+'">';

			for (var j = 0; j < 7; j++)
			{
				d = month_start.getUTCDate();
				var wd = month_start.getUTCDay();
				var className = 'bx-calendar-cell';

				if (cur_month != month_start.getUTCMonth())
					className += ' bx-calendar-date-hidden';
				else if (cur_day == d)
					className += ' bx-calendar-active';


				if (wd == 0 || wd == 6)
					className += ' bx-calendar-weekend';

				s += '<a href="javascript:void(0)" class="'+className+'" data-date="' + month_start.valueOf() + '">' + d + '</a>';

				month_start.setUTCDate(month_start.getUTCDate()+1);
			}
			s += '</div>';
		}

		l.innerHTML = s;

		return l;
	};

	this._prev = function()
	{
		this.SetMonth(this.value.getUTCMonth()-1);
	};

	this._next = function()
	{
		this.SetMonth(this.value.getUTCMonth()+1);
	};

	this._menu_month_content = function()
	{
		var months = '', cur_month = this.value.getMonth(), i;
		for (i = 0; i < 12; i++)
		{
			months += '<span class="bx-calendar-month'+(i == cur_month ? ' bx-calendar-month-active' : '')+'" data-bx-month="' + i + '">' + BX.message('MONTH_' + (i + 1)) + '</span>';
		}

		return '<div class="bx-calendar-month-popup"><div class="bx-calendar-month-title" data-bx-month="' + this.value.getUTCMonth() + '">' + BX.message('MONTH_' + (this.value.getUTCMonth() + 1)) + '</div><div class="bx-calendar-month-content">' + months + '</div></div>';
	};

	this._menu_month = function()
	{
		if (!this.popup_month)
		{
			this.popup_month = new BX.PopupWindow(
				'calendar_popup_month_' + Math.random(), this.PARTS.MONTH,
				{
					content: this._menu_month_content(),
					closeByEsc: true,
					autoHide: true,
					offsetTop: -29,
					offsetLeft: -1,
					className: this.month_popup_classname,
					events: {
						onPopupShow: BX.delegate(function() {
							if (this.popup_year)
							{
								this.popup_year.close();
							}
						}, this)
					}
				}
			);

			BX.bind(this.popup_month.popupContainer, 'click', BX.proxy(this.month_popup_click, this));
			this.popup_month.BXMONTH = this.value.getUTCMonth();
		}
		else if (this.popup_month.BXMONTH != this.value.getUTCMonth())
		{
			this.popup_month.setContent(this._menu_month_content());
			this.popup_month.BXMONTH = this.value.getUTCMonth();
		}

		this.popup_month.show();
	};

	this.month_popup_click = function(e)
	{
		var target = e.target || e.srcElement;
		if (target && target.getAttribute && target.getAttribute('data-bx-month'))
		{
			this.SetMonth(parseInt(target.getAttribute('data-bx-month')));
			this.popup_month.close();
		}
	};

	this._menu_year_content = function()
	{
		var s = '<div class="bx-calendar-year-popup"><div class="bx-calendar-year-title" data-bx-year="' + this.value.getUTCFullYear() + '">' + this.value.getUTCFullYear() + '</div><div class="bx-calendar-year-content" id="bx-calendar-year-content">';

		for (var i=-3; i <= 3; i++)
		{
			s += '<span class="bx-calendar-year-number' + (i == 0?' bx-calendar-year-active' : '') + '" data-bx-year="' + (this.value.getUTCFullYear() - i) + '">' + (this.value.getUTCFullYear() - i)+'</span>';
		}

		s += '</div><input data-bx-year-input="Y" type="text" class="bx-calendar-year-input" maxlength="4" /></div>';

		return s;
	};

	this._menu_year = function()
	{
		if (!this.popup_year)
		{
			this.popup_year = new BX.PopupWindow(
				'calendar_popup_year_' + Math.random(), this.PARTS.YEAR,
				{
					content: this._menu_year_content(),
					closeByEsc: true,
					autoHide: true,
					offsetTop: -29,
					offsetLeft: -1,
					className: this.year_popup_classname,
					events: {
						onPopupShow: BX.delegate(function() {
							if (this.popup_month)
							{
								this.popup_month.close();
							}
						}, this)
					}
				}
			);

			BX.bind(this.popup_year.popupContainer, 'click', BX.proxy(this.year_popup_click, this));
			BX.bind(this.popup_year.popupContainer, 'keyup', BX.proxy(this.year_popup_keyup, this));
			this.popup_year.BXYEAR = this.value.getUTCFullYear();
		}
		else if (this.popup_year.BXYEAR != this.value.getUTCFullYear())
		{
			this.popup_year.setContent(this._menu_year_content());
			this.popup_year.BXYEAR = this.value.getUTCFullYear();
		}

		this.popup_year.show();
	};

	this.year_popup_click = function(e)
	{
		var target = e.target || e.srcElement;
		if (target && target.getAttribute && target.getAttribute('data-bx-year'))
		{
			this.SetYear(parseInt(target.getAttribute('data-bx-year')));
			this.popup_year.close();
		}
	};
	this.year_popup_keyup = function(e)
	{
		var target = e.target || e.srcElement;
		if (target && target.getAttribute && target.getAttribute('data-bx-year-input') == 'Y')
		{
			var value = parseInt(target.value);
			if(value >= 1900 && value <= 2100)
			{
				this.SetYear(value);
				this.popup_year.close();
			}
		}
	};

	this._check_date = function(v)
	{
		var res = v;

		if (BX.type.isString(v))
		{
			res = BX.parseDate(v, true);
		}

		if (!BX.type.isDate(res) || isNaN(res.valueOf()))
		{
			res = BX.date.convertToUTC(new Date());
			if (this.params.bHideTime)
			{
				res.setUTCHours(0);
				res.setUTCMinutes(0);
			}
		}

		res.setUTCMilliseconds(0);
		res.setUTCSeconds(0);

		res.BXCHECKED = true;

		return res;
	};
};

BX.JCCalendar.prototype.Show = function(params)
{
	if (!BX.isReady)
	{
		BX.ready(BX.delegate(function() {this.Show(params)}, this));
		return;
	}

	params.node = params.node||document.body;

	if (BX.type.isNotEmptyString(params.node))
	{
		var n = BX(params.node);
		if (!n)
		{
			n = document.getElementsByName(params.node);
			if (n && n.length > 0)
			{
				n = n[0]
			}
		}
		params.node = n;
	}

	if (!params.node)
		return;

	if (!!params.field)
	{
		if (BX.type.isString(params.field))
		{
			n = BX(params.field);
			if (!!n)
			{
				params.field = n;
			}
			else
			{
				if (params.form)
				{
					if (BX.type.isString(params.form))
					{
						params.form = document.forms[params.form];
					}
				}

				if (BX.type.isDomNode(params.form) && !!params.form[params.field])
				{
					params.field = params.form[params.field];
				}
				else
				{
					n = document.getElementsByName(params.field);
					if (n && n.length > 0)
					{
						n = n[0];
						params.field = n;
					}
				}
			}

			if (BX.type.isString(params.field))
			{
				params.field = BX(params.field);
			}
		}
	}

	var bShow = !this.popup || !this.popup.isShown() || this.params.node != params.node;

	this.params = params;

	this.params.bCompatibility = (typeof this.params.bCompatibility == 'undefined' ? false : this.params.bCompatibility);
	this.params.bTimeVisibility = (typeof this.params.bTimeVisibility == 'undefined' ? !this.params.bCompatibility : this.params.bTimeVisibility);
	if (this.params.bCompatibility)
	{
		this.params.bCategoryTimeVisibilityOption = (
			this.params.bCategoryTimeVisibilityOption ? this.params.bCategoryTimeVisibilityOption : ''
		);
		this.params.bNameTimeVisibilityOption = (
			this.params.bNameTimeVisibilityOption ? this.params.bNameTimeVisibilityOption : 'time_visibility'
		);

		if (typeof this._bTimeVisibility !== 'undefined')
		{
			this.params.bTimeVisibility = this._bTimeVisibility;
		}
	}

	this.params.bTime = typeof this.params.bTime == 'undefined' ? true : !!this.params.bTime;
	this.params.bHideTime = typeof this.params.bHideTime == 'undefined' ? true : !!this.params.bHideTime;
	this.params.bUseSecond = typeof this.params.bUseSecond == 'undefined' ? true : !!this.params.bUseSecond;

	this.weekStart = parseInt(this.params.weekStart || this.params.weekStart || BX.message('WEEK_START'));
	if (isNaN(this.weekStart))
		this.weekStart = 1;

	if (!this.popup)
	{
		this._create(this.params);
	}
	else
	{
		this.popup.setBindElement(this.params.node);
	}

	var bHideTime = !!this.params.bHideTime;
	if (this.params.value)
	{
		this.SetValue(this.params.value);
		bHideTime = this.value.getUTCHours() <= 0 && this.value.getUTCMinutes() <= 0;
	}
	else if (this.params.field)
	{
		this.SetValue(this.params.field.value);
		bHideTime = this.value.getUTCHours() <= 0 && this.value.getUTCMinutes() <= 0;
	}
	else if (!!this.params.currentTime)
	{
		this.SetValue(this.params.currentTime);
	}
	else
	{
		this.SetValue();
	}

	if (!!this.params.bTime)
	{
		this.activateTimeStyle(bHideTime);
	}
	else
	{
		this.activateDateStyle(bHideTime);
	}

	if (bShow)
	{
		this._auto_hide_disable();
		this.popup.show();
		setTimeout(BX.proxy(this._auto_hide_enable, this), 0);
	}

	this.params.bSetFocus = typeof this.params.bSetFocus == 'undefined' ? true : !!this.params.bSetFocus;
	if(this.params.bSetFocus)
	{
		params.node.blur();
	}
	else
	{
		BX.bind(params.node, 'keyup', BX.defer(function(){
			this.SetValue(params.node.value);
			if(!!this.params.bTime)
			{
				if(this.value.getUTCHours() <= 0 && this.value.getUTCMinutes() <= 0)
					BX.removeClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
				else
					BX.addClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
			}
		}, this));
	}

	return this;
};

BX.JCCalendar.prototype.activateDateStyle = function(bHideTime)
{
	BX.addClass(this.DIV, 'bx-calendar-time-disabled');

	if (!!bHideTime)
		BX.removeClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
	else
		BX.addClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
};

BX.JCCalendar.prototype.activateTimeStyle = function(bHideTime)
{
	if (this.params.bCompatibility && !this.params.bTimeVisibility)
	{
		BX.addClass(this.PARTS.BUTTONS, 'bx-calendar-buttons-disabled');
		BX.addClass(this.PARTS.TIME, 'bx-calendar-set-time-wrap-simple');
		BX.removeClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
	}
	else
	{
		BX.removeClass(this.DIV, 'bx-calendar-time-disabled');

		if (!!bHideTime)
			BX.removeClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
		else
			BX.addClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
	}
};

BX.JCCalendar.prototype.SetDay = function(d)
{
	this.value.setUTCDate(d);
	return this.SetValue(this.value);
};

BX.JCCalendar.prototype.SetMonth = function(m)
{
	if (this.popup_month)
		this.popup_month.close();

	this.value.setUTCMonth(m);

	if(m < 0)
		m += 12;
	else if (m >= 12)
		m -= 12;

	while(this.value.getUTCMonth() > m)
	{
		this.value.setUTCDate(this.value.getUTCDate()-1);
	}

	return this.SetValue(this.value);
};

BX.JCCalendar.prototype.SetYear = function(y)
{
	if (this.popup_year)
		this.popup_year.close();
	this.value.setUTCFullYear(y);
	return this.SetValue(this.value);
};

BX.JCCalendar.prototype.SetDate = function(v, bSet)
{
	v = this._check_date(v);
	v.setUTCHours(this.value.getUTCHours());
	v.setUTCMinutes(this.value.getUTCMinutes());
	v.setUTCSeconds(this.value.getUTCSeconds());

	if (this.params.bTime && !bSet)
	{
		return this.SetValue(v);
	}
	else
	{
		this.SetValue(v);
		this.SaveValue();
	}
};

BX.JCCalendar.prototype.SetValue = function(v)
{
	this.value = (v && v.BXCHECKED) ? v : this._check_date(v);

	this.PARTS.MONTH.innerHTML = BX.message('MONTH_' + (this.value.getUTCMonth()+1));
	this.PARTS.YEAR.innerHTML = this.value.getUTCFullYear();

	if (!!this.params.bTime)
	{
		var h = this.value.getUTCHours();
		if (this.bAmPm)
		{
			if (h >= 12)
			{
				this.PARTS.TIME_AMPM.innerHTML = 'PM';

				if (h != 12)
					h -= 12;
			}
			else
			{
				this.PARTS.TIME_AMPM.innerHTML = 'AM';

				if (h == 0)
					h = 12;
			}
		}

		this.PARTS.TIME_INPUT_H.value = BX.util.str_pad_left(h.toString(), 2, "0");
		this.PARTS.TIME_INPUT_M.value = BX.util.str_pad_left(this.value.getUTCMinutes().toString(), 2, "0");
	}

	this._set_layer();

	return this;
};

BX.JCCalendar.prototype.SaveValue = function()
{
	if (this.popup_month)
		this.popup_month.close();
	if (this.popup_year)
		this.popup_year.close();

	var bSetValue = true;
	if (!!this.params.callback)
	{
		var res = this.params.callback.apply(this, [new Date(this.value.valueOf()+this.value.getTimezoneOffset()*60000)]);
		if (res === false)
			bSetValue = false;
	}

	if (bSetValue)
	{
		var bTime = !!this.params.bTime && BX.hasClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');

		if (this.params.field)
		{
			var format = BX.message(bTime ? 'FORMAT_DATETIME' : 'FORMAT_DATE');

			if(bTime && !this.params.bUseSecond)
			{
				format = format.replace(':SS', '');
			}

			this.params.field.value = BX.calendar.ValueToStringFormat(this.value, format, true);
			BX.fireEvent(this.params.field, 'change');
		}

		this.popup.close();

		if (!!this.params.callback_after)
		{
			this.params.callback_after.apply(this, [new Date(this.value.valueOf()+this.value.getTimezoneOffset()*60000), bTime]);
		}
	}

	return this;
};

BX.JCCalendar.prototype.Close = function()
{
	if (!!this.popup)
		this.popup.close();

	return this;
};

BX.JCSpinner = function(params)
{
	params = params || {};
	this.params = {
		input: params.input || null,

		delta: params.delta || 1,

		timeout_start: params.timeout_start || 1000,
		timeout_cont: params.timeout_cont || 150,

		callback_start: params.callback_start || null,
		callback_change: params.callback_change || null,
		callback_finish: params.callback_finish || null,

		bSaveValue: typeof params.bSaveValue == 'undefined' ? !!params.input : !!params.bSaveValue
	};

	this.mousedown = false;
	this.direction = 1;
};

BX.JCSpinner.prototype.Show = function()
{
	this.node = BX.create('span', {
		events: {
			mousedown: BX.delegateEvent(
				{attr: 'data-dir'},
				BX.delegate(this.Start, this)
			)
		},
		html: '<a href="javascript:void(0)" class="bx-calendar-form-arrow bx-calendar-form-arrow-top" data-dir="1"><i></i></a><a href="javascript:void(0)" class="bx-calendar-form-arrow bx-calendar-form-arrow-bottom" data-dir="-1"><i></i></a>'
	});
	return this.node;
};

BX.JCSpinner.prototype.Start = function()
{
	this.mousedown = true;
	this.direction = BX.proxy_context.getAttribute('data-dir') > 0 ? 1 : -1;
	BX.bind(document, "mouseup", BX.proxy(this.MouseUp, this));
	this.ChangeValue(true);
};

BX.JCSpinner.prototype.ChangeValue = function(bFirst)
{
	if(!this.mousedown)
		return;

	if (this.params.input)
	{
		var v = parseInt(this.params.input.value, 10) + this.params.delta * this.direction;

		if (this.params.bSaveValue)
			this.params.input.value = v;

		if (!!bFirst && this.params.callback_start)
			this.params.callback_start(this.params, v, this.direction);

		if (this.params.callback_change)
			this.params.callback_change(this.params, v, this.direction);

		setTimeout(
			BX.proxy(this.ChangeValue, this),
			!!bFirst ? this.params.timeout_start : this.params.timeout_cont
		);
	}
};

BX.JCSpinner.prototype.MouseUp = function()
{
	this.mousedown = false;
	BX.unbind(document, "mouseup", BX.proxy(this.MouseUp, this));

	if (this.params.callback_finish)
		this.params.callback_finish(this.params, this.params.input.value);
};

/**************** compatibility hacks ***************************/

window.jsCalendar = {
	Show: function(obj, field, fieldFrom, fieldTo, bTime, serverTime, form_name, bHideTimebar)
	{
		return BX.calendar({
			node: obj, field: field, form: form_name, bTime: !!bTime, currentTime: serverTime, bHideTimebar: !!bHideTimebar
		});
	},

	ValueToString: BX.calendar.ValueToString
};


/************ clock popup transferred from timeman **************/

BX.CClockSelector = function(params)
{
	this.params = params;

	this.params.popup_buttons = this.params.popup_buttons || [
		new BX.PopupWindowButton({
			text : BX.message('CAL_BUTTON'),
			className : "popup-window-button-create",
			events : {click : BX.proxy(this.setValue, this)}
		})
	];

	this.isReady = false;

	this.WND = new BX.PopupWindow(
		this.params.popup_id || 'clock_selector_popup',
		this.params.node,
		this.params.popup_config || {
			titleBar: BX.message('CAL_TIME'),
			offsetLeft: -45,
			offsetTop: -135,
			autoHide: true,
			closeIcon: true,
			closeByEsc: true
		}
	);

	this.SHOW = false;
	BX.addCustomEvent(this.WND, "onPopupClose", BX.delegate(this.onPopupClose, this));

	this.obClocks = {};
	this.CLOCK_ID = this.params.clock_id || 'clock_selector';
};

BX.CClockSelector.prototype.Show = function()
{
	if (!this.isReady)
	{
		//BX.timeman.showWait(this.parent.DIV);

		BX.addCustomEvent('onClockRegister', BX.proxy(this.onClockRegister, this));
		return BX.ajax.get('/bitrix/tools/clock_selector.php', {start_time: this.params.start_time, clock_id: this.CLOCK_ID, sessid: BX.bitrix_sessid()}, BX.delegate(this.Ready, this));
	}

	this.WND.setButtons(this.params.popup_buttons);
	this.WND.show();

	this.SHOW = true;

	if (window['bxClock_' + this.obClocks[this.CLOCK_ID]])
	{
		setTimeout("window['bxClock_" + this.obClocks[this.CLOCK_ID] + "'].CalculateCoordinates()", 40);
	}

	return true;
};

BX.CClockSelector.prototype.onClockRegister = function(obClocks)
{
	if (obClocks[this.CLOCK_ID])
	{
		this.obClocks[this.CLOCK_ID] = obClocks[this.CLOCK_ID];
		BX.removeCustomEvent('onClockRegister', BX.proxy(this.onClockRegister, this));
	}
};

BX.CClockSelector.prototype.Ready = function(data)
{
	this.content = this.CreateContent(data);
	this.WND.setContent(this.content);

	this.isReady = true;
	//BX.timeman.closeWait();

	setTimeout(BX.proxy(this.Show, this), 30);
};

BX.CClockSelector.prototype.CreateContent = function(data)
{
	return BX.create('DIV', {
		events: {click: BX.PreventDefault},
		html:
			'<div class="bx-tm-popup-clock">' + data + '</div>'
	});
};

BX.CClockSelector.prototype.setValue = function(e)
{
	if (this.params.callback)
	{
		var input = BX.findChild(this.content, {tagName: 'INPUT'}, true);
		this.params.callback.apply(this.params.node, [input.value]);
	}

	return BX.PreventDefault(e);
};

BX.CClockSelector.prototype.closeWnd = function(e)
{
	this.WND.close();
	return (e || window.event) ? BX.PreventDefault(e) : true;
};

BX.CClockSelector.prototype.setNode = function(node)
{
	this.WND.setBindElement(node);
};

BX.CClockSelector.prototype.setTime = function(timestamp)
{
	this.params.start_time = timestamp;
	if (window['bxClock_' + this.obClocks[this.CLOCK_ID]])
	{
		window['bxClock_' +  this.obClocks[this.CLOCK_ID]].SetTime(parseInt(timestamp/3600), parseInt((timestamp%3600)/60));
	}
};

BX.CClockSelector.prototype.setCallback = function(cb)
{
	this.params.callback = cb;
};

BX.CClockSelector.prototype.onPopupClose = function()
{
	this.SHOW = false;
};

})();

/* End */
;
; /* Start:"a:4:{s:4:"full";s:53:"/bitrix/js/main/core/core_timer.min.js?17455639674311";s:6:"source";s:34:"/bitrix/js/main/core/core_timer.js";s:3:"min";s:38:"/bitrix/js/main/core/core_timer.min.js";s:3:"map";s:38:"/bitrix/js/main/core/core_timer.map.js";}"*/
(function(t){if(t.BX.timer)return;var i=[],e=200,a=null,s=0;BX.timer=function(t,i){i=i||{};if(BX.type.isString(t)||BX.type.isElementNode(t))i.container=t;else if(typeof t=="object")i=t;if(!i.container)return false;var s=new BX.CTimer(i);BX.timer.start(s);if(null==a){a=setInterval(r,e);BX.garbage(BX.timer.clear)}return s};BX.timer.stop=function(t){i[t.TIMER_INDEX]=null};BX.timer.start=function(t){t.TIMER_INDEX=s;i[s++]=t};BX.timer.clock=function(t,i){return BX.timer({container:t,dt:i})};BX.timer.clear=function(){clearInterval(a);i=null};BX.timer.registerFormat=function(t,i){BX.CTimer.prototype.formatValueHandlers[t]=i};BX.timer.getHandler=function(t){return BX.CTimer.prototype.formatValueHandlers[t]};BX.CTimer=function(t,i){this.container=t.container;this.from=t.from?parseInt(t.from.valueOf()):null;this.to=t.to?parseInt(t.to.valueOf()):null;this.index=i;this.dt=parseInt(t.dt);if(isNaN(this.dt))this.dt=0;this.display=t.display||(BX.isAmPmMode()?"clock_am_pm":"clock");this.accuracy=t.accuracy||60;this.callback=this.from?this._callback_from:this.to?this._callback_to:this._callback;this.callback_finish=t.callback_finish;this.formatValue=this.formatValueHandlers.clock;this.bInited=false;BX.ready(BX.delegate(this.Init,this))};BX.CTimer.prototype.Init=function(){if(this.bInited)return;this.container=BX(this.container);this.container_value_fld=this.container.tagName.toUpperCase()=="INPUT"?"value":"innerHTML";if(this.container_value_fld=="value"&&(this.display=="clock"||this.display=="clock_am_pm")){if(this.display=="clock"){this.display="simple"}else if(this.display=="clock_am_pm"){this.display="simple_am_pm"}}this.formatValue=this.formatValueHandlers[this.display]?this.formatValueHandlers[this.display]:this.formatValueHandlers.clock;this.bInited=true};BX.CTimer.prototype.setFrom=function(t){if(!this.from)return;this.from=t};BX.CTimer.prototype.setTo=function(t){if(!this.to)return;this.to=t};BX.CTimer.prototype._callback=function(t){if(this.dt!==0)var t=new Date(t.valueOf()+this.dt);this.setValue(this.formatValue(t.getHours(),t.getMinutes(),t.getSeconds()))};BX.CTimer.prototype._callback_from=function(t){var i=(t.valueOf()-this.from.valueOf()+this.dt)/1e3;this.setValue(this.formatValue(parseInt(i/3600),parseInt(i%3600/60),parseInt(i%60)))};BX.CTimer.prototype._callback_to=function(t){var i=(this.to.valueOf()-t.valueOf())/1e3;if(i>0){this.setValue(this.formatValue(parseInt(i/3600),parseInt(i%3600/60),parseInt(i%60)))}else{this.Finish()}};BX.CTimer.prototype.formatValueHandlers={clock:function(t,i,e){var a='<span class="bx-timer-semicolon">:</span>';return BX.util.str_pad(t,2,"0","left")+a+(this.accuracy>=3600?"00":BX.util.str_pad(i,2,"0","left"))+(this.accuracy>=60?"":a+BX.util.str_pad(e,2,"0","left"))},clock_am_pm:function(t,i,e){var a="am";var s='<span class="bx-timer-semicolon">:</span>';if(t>12){t=t-12;a="pm"}else if(t==0){t=12;a="am"}else if(t==12){a="pm"}return t+s+(this.accuracy>=3600?"00":BX.util.str_pad(i,2,"0","left"))+(this.accuracy>=60?"":s+BX.util.str_pad(e,2,"0","left"))+" "+a},simple:function(t,i,e){return BX.util.str_pad(t,2,"0","left")+":"+(this.accuracy>=3600?"00":BX.util.str_pad(i,2,"0","left"))+(this.accuracy>=60?"":":"+BX.util.str_pad(e,2,"0","left"))},simple_am_pm:function(t,i,e){var a="am";if(t>12){t=t-12;a="pm"}else if(t==0){t=12;a="am"}else if(t==12){a="pm"}return t+":"+(this.accuracy>=3600?"00":BX.util.str_pad(i,2,"0","left"))+(this.accuracy>=60?"":":"+BX.util.str_pad(e,2,"0","left"))+" "+a},worktime:function(t,i,e){return t+BX.message("JS_CORE_H")+" "+(this.accuracy>=3600?"":i+BX.message("JS_CORE_M")+(this.accuracy>=60?"":" "+e+BX.message("JS_CORE_S")))},worktime_short:function(t,i,e){return BX.util.rtrim((t>0?t+BX.message("JS_CORE_H")+" ":"")+(i>0&&this.accuracy<3600?i+BX.message("JS_CORE_M")+" ":"")+(this.accuracy>=60?"":e>0?e+BX.message("JS_CORE_S"):""))}};BX.CTimer.prototype.setValue=function(t){if(this.bInited){if(t!=this._last_value)this.container[this.container_value_fld]=t;this._last_value=t}};BX.CTimer.prototype.Finish=function(){BX.timer.stop(this);if(this.callback_finish)this.callback_finish.apply(this);BX.cleanNode(this.container.parentNode)};function r(){var t=new Date;for(var e=0,a=s;e<a;e++){if(i[e]&&i[e].callback)i[e].callback.apply(i[e],[t])}t=null}})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:41:"/bitrix/js/main/dd.min.js?174556396611114";s:6:"source";s:21:"/bitrix/js/main/dd.js";s:3:"min";s:25:"/bitrix/js/main/dd.min.js";s:3:"map";s:25:"/bitrix/js/main/dd.map.js";}"*/
(function(){if(window.jsDD)return;jsDD={arObjects:[],arDestinations:[],arDestinationsPriority:[],arContainers:[],arContainersPos:[],current_dest_index:false,current_node:null,wndSize:null,bStarted:false,bDisable:false,bDisableDestRefresh:false,bEscPressed:false,bScrollWindow:false,scrollViewTimer:null,scrollViewConfig:{checkerTimeout:30,scrollZone:25,scrollBy:25,scrollContainer:null,bScrollH:true,bScrollV:true,pos:null},setScrollWindow:function(s){jsDD.bScrollWindow=!!s;if(BX.type.isDomNode(s)){jsDD.scrollViewConfig.scrollContainer=s;jsDD.scrollViewConfig.pos=BX.pos(s);var e=BX.style(s,"overflow")||"visible",D=BX.style(s,"overflow-x")||"visible",r=BX.style(s,"overflow-y")||"visible";jsDD.scrollViewConfig.bScrollH=e!="visible"||D!="visible";jsDD.scrollViewConfig.bScrollV=e!="visible"||r!="visible"}},Reset:function(){jsDD.arObjects=[];jsDD.arDestinations=[];jsDD.arDestinationsPriority=[];jsDD.bStarted=false;jsDD.current_node=null;jsDD.current_dest_index=false;jsDD.bDisableDestRefresh=false;jsDD.bDisable=false;jsDD.x=null;jsDD.y=null;jsDD.start_x=null;jsDD.start_y=null;jsDD.wndSize=null;jsDD.bEscPressed=false;clearInterval(jsDD.scrollViewTimer);jsDD.bScrollWindow=false;jsDD.scrollViewTimer=null;jsDD.scrollViewConfig.scrollContainer=null},registerObject:function(s){BX.bind(s,"mousedown",jsDD.startDrag);BX.Event.bind(s,"touchstart",jsDD.startDrag,{passive:true});s.__bxddid=jsDD.arObjects.length;jsDD.arObjects[s.__bxddid]=s},unregisterObject:function(s){if(typeof s["__bxddid"]==="undefined"){return}delete jsDD.arObjects[s.__bxddid];delete s.__bxddid;BX.unbind(s,"mousedown",jsDD.startDrag);BX.unbind(s,"touchstart",jsDD.startDrag)},registerDest:function(s,e){if(!e)e=100;s.__bxddeid=jsDD.arDestinations.length;s.__bxddpriority=e;jsDD.arDestinations[s.__bxddeid]=s;if(!jsDD.arDestinationsPriority[e])jsDD.arDestinationsPriority[e]=[s.__bxddeid];else jsDD.arDestinationsPriority[e].push(s.__bxddeid);jsDD.refreshDestArea(s.__bxddeid)},unregisterDest:function(s){if(typeof s["__bxddeid"]==="undefined"){return}delete jsDD.arDestinations[s.__bxddeid];delete s.__bxddeid;delete s.__bxddpriority;jsDD.refreshDestArea()},disableDest:function(s){if(typeof s.__bxddeid!=="undefined"){s.__bxdddisabled=true}},enableDest:function(s){if(typeof s.__bxddeid!=="undefined"){s.__bxdddisabled=false}},registerContainer:function(s){jsDD.arContainers[jsDD.arContainers.length]=s},getContainersScrollPos:function(s,e){var D={left:0,top:0};for(var r=0,n=jsDD.arContainers.length;r<n;r++){if(jsDD.arContainers[r]&&s>=jsDD.arContainersPos[r]["left"]&&s<=jsDD.arContainersPos[r]["right"]&&e>=jsDD.arContainersPos[r]["top"]&&e<=jsDD.arContainersPos[r]["bottom"]){D.left=jsDD.arContainers[r].scrollLeft;D.top=jsDD.arContainers[r].scrollTop}}return D},setContainersPos:function(){for(var s=0,e=jsDD.arContainers.length;s<e;s++){if(jsDD.arContainers[s])jsDD.arContainersPos[s]=BX.pos(jsDD.arContainers[s])}},refreshDestArea:function(s){if(s&&typeof s=="object"&&typeof s.__bxddeid!="undefined"){s=s.__bxddeid}if(typeof s=="undefined"){for(var e=0,D=jsDD.arDestinations.length;e<D;e++){jsDD.refreshDestArea(e)}}else{if(null==jsDD.arDestinations[s])return;var r=BX.pos(jsDD.arDestinations[s]);jsDD.arDestinations[s].__bxpos=[r.left,r.top,r.right,r.bottom]}},_checkEsc:function(s){s=s||window.event;if(jsDD.bStarted&&s.keyCode==27){jsDD.stopCurrentDrag()}},stopCurrentDrag:function(){if(jsDD.bStarted){jsDD.bEscPressed=true;jsDD.stopDrag()}},_onscroll:function(){jsDD.wndSize=BX.GetWindowSize()},_checkScroll:function(){if(jsDD.bScrollWindow){var s={clientX:jsDD.x-jsDD.wndSize.scrollLeft,clientY:jsDD.y-jsDD.wndSize.scrollTop},e=false,D=jsDD.scrollViewConfig.scrollZone;if(s.clientY<D&&jsDD.wndSize.scrollTop>0){window.scrollBy(0,-jsDD.scrollViewConfig.scrollBy);e=true}if(s.clientY>jsDD.wndSize.innerHeight-D&&jsDD.wndSize.scrollTop<jsDD.wndSize.scrollHeight-jsDD.wndSize.innerHeight){window.scrollBy(0,jsDD.scrollViewConfig.scrollBy);e=true}if(s.clientX<D&&jsDD.wndSize.scrollLeft>0){window.scrollBy(-jsDD.scrollViewConfig.scrollBy,0);e=true}if(s.clientX>jsDD.wndSize.innerWidth-D&&jsDD.wndSize.scrollLeft<jsDD.wndSize.scrollWidth-jsDD.wndSize.innerWidth){window.scrollBy(jsDD.scrollViewConfig.scrollBy,0);e=true}if(jsDD.scrollViewConfig.scrollContainer){var r=jsDD.scrollViewConfig.scrollContainer;if(jsDD.scrollViewConfig.bScrollH){if(s.clientX+jsDD.wndSize.scrollLeft<jsDD.scrollViewConfig.pos.left+D&&r.scrollLeft>0){r.scrollLeft-=jsDD.scrollViewConfig.scrollBy;e=true}if(s.clientX+jsDD.wndSize.scrollLeft>jsDD.scrollViewConfig.pos.right-D&&r.scrollLeft<r.scrollWidth-r.offsetWidth){r.scrollLeft+=jsDD.scrollViewConfig.scrollBy;e=true}}if(jsDD.scrollViewConfig.bScrollV){if(s.clientY+jsDD.wndSize.scrollTop<jsDD.scrollViewConfig.pos.top+D&&r.scrollTop>0){r.scrollTop-=jsDD.scrollViewConfig.scrollBy;e=true}if(s.clientY+jsDD.wndSize.scrollTop>jsDD.scrollViewConfig.pos.bottom-D&&r.scrollTop<r.scrollHeight-r.offsetHeight){r.scrollTop+=jsDD.scrollViewConfig.scrollBy;e=true}}}if(e){jsDD._onscroll();jsDD.drag(s)}}},startDrag:function(s){if(jsDD.bDisable)return true;s=s||window.event;if(!(BX.getEventButton(s)&BX.MSLEFT))return true;jsDD.current_node=null;if(s.currentTarget){jsDD.current_node=s.currentTarget;if(null==jsDD.current_node||null==jsDD.current_node.__bxddid){jsDD.current_node=null;return}}else{jsDD.current_node=s.srcElement;if(null==jsDD.current_node)return;while(null==jsDD.current_node.__bxddid){jsDD.current_node=jsDD.current_node.parentNode;if(jsDD.current_node.tagName=="BODY")return}}jsDD.bStarted=false;jsDD.bPreStarted=true;jsDD.wndSize=BX.GetWindowSize();jsDD.start_x=s.clientX+jsDD.wndSize.scrollLeft;jsDD.start_y=s.clientY+jsDD.wndSize.scrollTop;BX.bind(document,"mouseup",jsDD.stopDrag);BX.bind(document,"touchend",jsDD.stopDrag);BX.bind(document,"mousemove",jsDD.drag);BX.bind(document,"touchmove",jsDD.drag);BX.bind(window,"scroll",jsDD._onscroll);if(document.body.setCapture)document.body.setCapture();if(!jsDD.bDisableDestRefresh)jsDD.refreshDestArea();jsDD.setContainersPos();if(s.type!=="touchstart"){jsDD.denySelection();return BX.PreventDefault(s)}else{return true}},start:function(){if(jsDD.bDisable)return true;document.body.style.cursor="move";if(jsDD.current_node.onbxdragstart)jsDD.current_node.onbxdragstart();for(var s=0,e=jsDD.arDestinations.length;s<e;s++){if(jsDD.arDestinations[s]&&jsDD.arDestinations[s].onbxdestdragstart)jsDD.arDestinations[s].onbxdestdragstart(jsDD.current_node)}jsDD.bStarted=true;jsDD.bPreStarted=false;if(jsDD.bScrollWindow){if(jsDD.scrollViewTimer)clearInterval(jsDD.scrollViewTimer);jsDD.scrollViewTimer=setInterval(jsDD._checkScroll,jsDD.scrollViewConfig.checkerTimeout)}BX.bind(document,"keypress",this._checkEsc)},drag:function(s){if(jsDD.bDisable)return true;s=s||window.event;jsDD.x=s.clientX+jsDD.wndSize.scrollLeft;jsDD.y=s.clientY+jsDD.wndSize.scrollTop;if(!jsDD.bStarted){var e=5;if(jsDD.x>=jsDD.start_x-e&&jsDD.x<=jsDD.start_x+e&&jsDD.y>=jsDD.start_y-e&&jsDD.y<=jsDD.start_y+e)return true;jsDD.start()}if(jsDD.current_node.onbxdrag){jsDD.current_node.onbxdrag(jsDD.x,jsDD.y,s)}var D=jsDD.getContainersScrollPos(jsDD.x,jsDD.y);var r=jsDD.searchDest(jsDD.x+D.left,jsDD.y+D.top);if(r!==jsDD.current_dest_index){if(jsDD.current_dest_index!==false){if(jsDD.current_node.onbxdraghout)jsDD.current_node.onbxdraghout(jsDD.arDestinations[jsDD.current_dest_index],jsDD.x,jsDD.y);if(jsDD.arDestinations[jsDD.current_dest_index].onbxdestdraghout)jsDD.arDestinations[jsDD.current_dest_index].onbxdestdraghout(jsDD.current_node,jsDD.x,jsDD.y)}if(r!==false){if(jsDD.current_node.onbxdraghover)jsDD.current_node.onbxdraghover(jsDD.arDestinations[r],jsDD.x,jsDD.y);if(jsDD.arDestinations[r].onbxdestdraghover)jsDD.arDestinations[r].onbxdestdraghover(jsDD.current_node,jsDD.x,jsDD.y)}}jsDD.current_dest_index=r},stopDrag:function(s){BX.unbind(document,"keypress",jsDD._checkEsc);s=s||window.event;jsDD.bPreStarted=false;if(jsDD.bStarted){if(!jsDD.bEscPressed){jsDD.x=s.clientX+jsDD.wndSize.scrollLeft;jsDD.y=s.clientY+jsDD.wndSize.scrollTop}if(null!=jsDD.current_node.onbxdragstop)jsDD.current_node.onbxdragstop(jsDD.x,jsDD.y,s);var e=jsDD.getContainersScrollPos(jsDD.x,jsDD.y);var D=jsDD.searchDest(jsDD.x+e.left,jsDD.y+e.top);if(false!==D){if(jsDD.bEscPressed){if(null!=jsDD.arDestinations[D].onbxdestdraghout){if(!jsDD.arDestinations[D].onbxdestdraghout(jsDD.current_node,jsDD.x,jsDD.y))D=false;else{if(null!=jsDD.current_node.onbxdragfinish)jsDD.current_node.onbxdragfinish(jsDD.arDestinations[D],jsDD.x,jsDD.y)}}}else{if(null!=jsDD.arDestinations[D].onbxdestdragfinish){if(!jsDD.arDestinations[D].onbxdestdragfinish(jsDD.current_node,jsDD.x,jsDD.y,s))D=false;else{if(null!=jsDD.current_node.onbxdragfinish)jsDD.current_node.onbxdragfinish(jsDD.arDestinations[D],jsDD.x,jsDD.y)}}}}if(false===D){if(null!=jsDD.current_node.onbxdragrelease)jsDD.current_node.onbxdragrelease(jsDD.x,jsDD.y)}else{for(var r=0,n=jsDD.arDestinations.length;r<n;r++){if(r!=D&&jsDD.arDestinations[r]&&null!=jsDD.arDestinations[r].onbxdestdragrelease)jsDD.arDestinations[r].onbxdestdragrelease(jsDD.current_node,jsDD.x,jsDD.y)}}for(var r=0,n=jsDD.arDestinations.length;r<n;r++){if(jsDD.arDestinations[r]&&null!=jsDD.arDestinations[r].onbxdestdragstop)jsDD.arDestinations[r].onbxdestdragstop(jsDD.current_node,jsDD.x,jsDD.y)}}if(document.body.releaseCapture)document.body.releaseCapture();BX.unbind(window,"scroll",jsDD._onscroll);BX.unbind(document,"mousemove",jsDD.drag);BX.unbind(document,"touchmove",jsDD.drag);BX.unbind(document,"keypress",jsDD._checkEsc);BX.unbind(document,"mouseup",jsDD.stopDrag);BX.unbind(document,"touchend",jsDD.stopDrag);jsDD.allowSelection();document.body.style.cursor="";jsDD.current_node=null;jsDD.current_dest_index=false;if(jsDD.bScrollWindow){if(jsDD.scrollViewTimer)clearInterval(jsDD.scrollViewTimer)}if(jsDD.bStarted&&!jsDD.bDisableDestRefresh)jsDD.refreshDestArea();jsDD.bStarted=false;jsDD.bEscPressed=false},searchDest:function(s,e){var D,r,n,t,o;for(D=0,r=jsDD.arDestinationsPriority.length;D<r;D++){if(jsDD.arDestinationsPriority[D]&&BX.type.isArray(jsDD.arDestinationsPriority[D])){for(n=0,t=jsDD.arDestinationsPriority[D].length;n<t;n++){o=jsDD.arDestinationsPriority[D][n];if(jsDD.arDestinations[o]&&!jsDD.arDestinations[o].__bxdddisabled){if(jsDD.arDestinations[o].__bxpos[0]<=s&&jsDD.arDestinations[o].__bxpos[2]>=s&&jsDD.arDestinations[o].__bxpos[1]<=e&&jsDD.arDestinations[o].__bxpos[3]>=e){return o}}}}}return false},allowSelection:function(){document.onmousedown=document.ontouchstart=null;var s=document.body;s.ondrag=null;s.onselectstart=null;s.style.MozUserSelect="";if(jsDD.current_node){jsDD.current_node.ondrag=null;jsDD.current_node.onselectstart=null;jsDD.current_node.style.MozUserSelect=""}},denySelection:function(){document.onmousedown=document.ontouchstart=BX.False;var s=document.body;s.ondrag=BX.False;s.onselectstart=BX.False;s.style.MozUserSelect="none";if(jsDD.current_node){jsDD.current_node.ondrag=BX.False;jsDD.current_node.onselectstart=BX.False;jsDD.current_node.style.MozUserSelect="none"}},Disable:function(){jsDD.bDisable=true},Enable:function(){jsDD.bDisable=false}}})();
/* End */
;
; /* Start:"a:4:{s:4:"full";s:50:"/bitrix/js/main/core/core_dd.min.js?17455639672188";s:6:"source";s:31:"/bitrix/js/main/core/core_dd.js";s:3:"min";s:35:"/bitrix/js/main/core/core_dd.min.js";s:3:"map";s:35:"/bitrix/js/main/core/core_dd.map.js";}"*/
(function(e){BX.DD=function(e){return new BX.DD.dragdrop(e)};BX.DD.allowSelection=function(){document.onmousedown=null;var e=document.body;e.ondrag=null;e.onselectstart=null;e.style.MozUserSelect=""};BX.DD.denySelection=function(){document.onmousedown=BX.False;var e=document.body;e.ondrag=BX.False;e.onselectstart=BX.False;e.style.MozUserSelect="none"};BX.DD.dragdrop=function(e){};BX.DD.dropFiles=function(e){if(BX.type.isElementNode(e)&&this.supported()){e.setAttribute("dropzone","copy f:*/*");this.DIV=e;this._timer=null;this._initEvents();this._cancelLeave=function(){if(this._timer!=null){clearTimeout(this._timer);this._timer=null}};this._prepareLeave=function(){this._cancelLeave();this._timer=setTimeout(BX.delegate(function(){BX.onCustomEvent(this,"dragLeave")},this),100)};return this}return false};BX.DD.dropFiles.prototype._initEvents=function(){BX.bind(this.DIV,"dragover",BX.proxy(this._dragOver,this));BX.bind(this.DIV,"dragenter",BX.proxy(this._dragEnter,this));BX.bind(this.DIV,"dragleave",BX.proxy(this._dragLeave,this));BX.bind(this.DIV,"dragexit",BX.proxy(this._dragExit,this));BX.bind(this.DIV,"drop",BX.proxy(this._drop,this))};BX.DD.dropFiles.prototype._dragEnter=function(e){BX.PreventDefault(e);this._cancelLeave();BX.onCustomEvent(this,"dragEnter",[e]);return true};BX.DD.dropFiles.prototype._dragExit=function(e){BX.PreventDefault(e);this._prepareLeave();return false};BX.DD.dropFiles.prototype._dragLeave=function(e){BX.PreventDefault(e);this._prepareLeave();return false};BX.DD.dropFiles.prototype._dragOver=function(e){BX.PreventDefault(e);this._cancelLeave();return true};BX.DD.dropFiles.prototype._drop=function(e){BX.PreventDefault(e);var t=e.dataTransfer;var r=t.files;BX.onCustomEvent(this,"dropFiles",[r,e]);BX.onCustomEvent(this,"dragLeave");return false};BX.DD.dropFiles.prototype.isEventSupported=function(e){var t=BX.create("DIV");var r="on"+e;var n=r in t;if(!n&&t.setAttribute&&t.removeAttribute){t.setAttribute(r,"");n=typeof t[r]==="function"}t=null;return n};BX.DD.dropFiles.prototype.supported=function(){return!!e.FileReader&&this.isEventSupported("dragstart")&&this.isEventSupported("drop")}})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:51:"/bitrix/js/main/core/core_window.js?174556396798766";s:6:"source";s:35:"/bitrix/js/main/core/core_window.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
;(function(window) {
if (BX.WindowManager) return;

/* windows manager */
BX.WindowManager = {
	_stack: [],
	_runtime_resize: {},
	_delta: 2,
	_delta_start: 1000,
	currently_loaded: null,

	settings_category: 'BX.WindowManager.9.5',

	register: function (w)
	{
		this.currently_loaded = null;

		w.WM_REG_INDEX = this._stack.length;
		this._stack.push(w);

		if (this._stack.length < 2)
		{
			BX.bind(document, 'keyup', BX.proxy(this.__checkKeyPress, this));
		}
	},

	unregister: function (w)
	{
		if (null == w.WM_REG_INDEX)
			return null;

		var _current;
		if (this._stack.length > 0)
		{
			while ((_current = this.__pop_stack()) != w)
			{
				if (!_current)
				{
					_current = null;
					break;
				}
			}

			if (this._stack.length <= 0)
			{
				this.enableKeyCheck();
			}

			return _current;
		}
		else
		{
			return null;
		}
	},

	__pop_stack: function(clean)
	{
		if (this._stack.length > 0)
		{
			var _current = this._stack.pop();
			_current.WM_REG_INDEX = null;
			BX.onCustomEvent(_current, 'onWindowUnRegister', [clean === true]);

			return _current;
		}
		else
			return null;
	},

	clean: function()
	{
		while (this.__pop_stack(true)){}
		this._stack = [];
		this.disableKeyCheck();
	},

	Get: function()
	{
		if (this.currently_loaded)
			return this.currently_loaded;
		else if (this._stack.length > 0)
			return this._stack[this._stack.length-1];
		else
			return null;
	},

	setStartZIndex: function(value)
	{
		this._delta_start = value;
	},

	restoreStartZIndex: function()
	{
		this._delta_start = 1000;
	},

	GetZIndex: function()
	{
		var _current;
		return (null != (_current = this._stack[this._stack.length-1])
			? parseInt(_current.Get().style.zIndex) + this._delta
			: this._delta_start
		);
	},

	__get_check_url: function(url)
	{
		var pos = url.indexOf('?');
		return pos == -1 ? url : url.substring(0, pos);
	},

	saveWindowSize: function(url, params)
	{
		var check_url = this.__get_check_url(url);
		if (BX.userOptions)
		{
			BX.userOptions.save(this.settings_category, 'size_' + check_url, 'width', params.width);
			BX.userOptions.save(this.settings_category, 'size_' + check_url, 'height', params.height);
		}

		this._runtime_resize[check_url] = params;
	},

	saveWindowOptions: function(wnd_id, opts)
	{
		if (BX.userOptions)
		{
			for (var i in opts)
			{
				if(opts.hasOwnProperty(i))
				{
					BX.userOptions.save(this.settings_category, 'options_' + wnd_id, i, opts[i]);
				}
			}
		}
	},

	getRuntimeWindowSize: function(url)
	{
		return this._runtime_resize[this.__get_check_url(url)];
	},

	disableKeyCheck: function()
	{
		BX.unbind(document, 'keyup', BX.proxy(this.__checkKeyPress, this));
	},

	enableKeyCheck: function()
	{
		BX.bind(document, 'keyup', BX.proxy(this.__checkKeyPress, this));
	},

	__checkKeyPress: function(e)
	{
		if (null == e)
			e = window.event;

		if (e.keyCode == 27)
		{
			var wnd = BX.WindowManager.Get();
			if (wnd && !wnd.unclosable) wnd.Close();
		}
	}
};

BX.garbage(BX.WindowManager.clean, BX.WindowManager);

/* base button class */
BX.CWindowButton = function(params)
{
	if (params.btn)
	{
		this.btn = params.btn;
		this.parentWindow = params.parentWindow;

		if (/save|apply/i.test(this.btn.name))
		{
			BX.bind(this.btn, 'click', BX.delegate(this.disableUntilError, this));
		}
	}
	else
	{
		this.title = params.title; // html value attr
		this.hint = params.hint; // html title attr
		this.id = params.id; // html name and id attrs
		this.name = params.name; // html name or value attrs when id and title 're absent
		this.className = params.className; // className for button input

		this.action = params.action;
		this.onclick = params.onclick;

		// you can override button creation method
		if (params.Button && BX.type.isFunction(params.Button))
			this.Button = params.Button;

		this.btn = null;
	}
};

BX.CWindowButton.prototype.disable = function()
{
	if (this.btn)
		this.parentWindow.showWait(this.btn);
};
BX.CWindowButton.prototype.enable = function(){
	if (this.btn)
		this.parentWindow.closeWait(this.btn);
};

BX.CWindowButton.prototype.emulate = function()
{
	if (this.btn && this.btn.disabled)
		return;

	var act =
		this.action
		? BX.delegate(this.action, this)
		: (
			this.onclick
			? this.onclick
			: (
				this.btn
				? this.btn.getAttribute('onclick')
				: ''
			)
		);

	if (act)
	{
		setTimeout(act, 50);
		if (this.btn && /save|apply/i.test(this.btn.name) && !this.action)
		{
			this.disableUntilError();
		}
	}
};

BX.CWindowButton.prototype.Button = function(parentWindow)
{
	this.parentWindow = parentWindow;

	var btn = {
		props: {
			'type': 'button',
			'name': this.id ? this.id : this.name,
			'value': this.title ? this.title : this.name,
			'id': this.id
		}
	};

	if (this.hint)
		btn.props.title = this.hint;
	if (!!this.className)
		btn.props.className = this.className;

	if (this.action)
	{
		btn.events = {
			'click': BX.delegate(this.action, this)
		};
	}
	else if (this.onclick)
	{
		if (BX.browser.IsIE())
		{
			btn.events = {
				'click': BX.delegate(function() {eval(this.onclick)}, this)
			};
		}
		else
		{
			btn.attrs = {
				'onclick': this.onclick
			};
		}
	}

	this.btn = BX.create('INPUT', btn);

	return this.btn;
};

BX.CWindowButton.prototype.disableUntilError = function() {
	this.disable();
	if (!this.__window_error_handler_set)
	{
		BX.addCustomEvent(this.parentWindow, 'onWindowError', BX.delegate(this.enable, this));
		this.__window_error_handler_set = true;
	}
};

/* base window class */
BX.CWindow = function(div, type)
{
	this.DIV = div || document.createElement('DIV');

	this.SETTINGS = {
		resizable: false,
		min_height: 0,
		min_width: 0,
		top: 0,
		left: 0,
		draggable: false,
		drag_restrict: true,
		resize_restrict: true
	};

	this.ELEMENTS = {
		draggable: [],
		resizer: [],
		close: []
	};

	this.type = type == 'float' ? 'float' : 'dialog';

	BX.adjust(this.DIV, {
		props: {
			className: 'bx-core-window'
		},
		style: {
			'zIndex': 0,
			'position': 'absolute',
			'display': 'none',
			'top': this.SETTINGS.top + 'px',
			'left': this.SETTINGS.left + 'px',
			'height': '100px',
			'width': '100px'
		}
	});

	this.isOpen = false;

	BX.addCustomEvent(this, 'onWindowRegister', BX.delegate(this.onRegister, this));
	BX.addCustomEvent(this, 'onWindowUnRegister', BX.delegate(this.onUnRegister, this));

	this.MOUSEOVER = null;
	BX.bind(this.DIV, 'mouseover', BX.delegate(this.__set_msover, this));
	BX.bind(this.DIV, 'mouseout', BX.delegate(this.__unset_msover, this));

	BX.ready(BX.delegate(function() {
		document.body.appendChild(this.DIV);
		BX.ZIndexManager.register(this.DIV);
	}, this));
};

BX.CWindow.prototype.Get = function () {return this.DIV};
BX.CWindow.prototype.visible = function() {return this.isOpen;};

BX.CWindow.prototype.Show = function(bNotRegister)
{
	this.DIV.style.display = 'block';

	if (!bNotRegister)
	{
		BX.WindowManager.register(this);
		BX.onCustomEvent(this, 'onWindowRegister');
	}

	BX.ZIndexManager.bringToFront(this.DIV);
};

BX.CWindow.prototype.Hide = function()
{
	BX.WindowManager.unregister(this);
	this.DIV.style.display = 'none';
};

BX.CWindow.prototype.onRegister = function()
{
	this.isOpen = true;
};

BX.CWindow.prototype.onUnRegister = function(clean)
{
	this.isOpen = false;

	if (clean || (this.PARAMS && this.PARAMS.content_url))
	{
		if (clean) {BX.onCustomEvent(this, 'onWindowClose', [this, true]);}

		if (this.DIV.parentNode)
			this.DIV.parentNode.removeChild(this.DIV);
	}
	else
	{
		this.DIV.style.display = 'none';
	}
};

BX.CWindow.prototype.CloseDialog = // compatibility
BX.CWindow.prototype.Close = function(bImmediately)
{
	BX.onCustomEvent(this, 'onBeforeWindowClose', [this]);
	if (bImmediately !== true)
	{
		if (this.denyClose)
			return false;
	}

	BX.onCustomEvent(this, 'onWindowClose', [this]);

	//this crashes vis editor in ie via onWindowResizeExt event handler
	//if (this.bExpanded) this.__expand();
	// alternative version:
	if (this.bExpanded)
	{
		var pDocElement = BX.GetDocElement();
		BX.unbind(window, 'resize', BX.proxy(this.__expand_onresize, this));
		pDocElement.style.overflow = this.__expand_settings.overflow;
	}

	BX.WindowManager.unregister(this);

	return true;
};

BX.CWindow.prototype.SetResize = function(elem)
{
	elem.style.cursor = 'se-resize';
	BX.bind(elem, 'mousedown', BX.proxy(this.__startResize, this));

	this.ELEMENTS.resizer.push(elem);
	this.SETTINGS.resizable = true;
};

BX.CWindow.prototype.SetExpand = function(elem, event_name)
{
	event_name = event_name || 'click';
	BX.bind(elem, event_name, BX.proxy(this.__expand, this));
};

BX.CWindow.prototype.__expand_onresize = function()
{
	var windowSize = BX.GetWindowInnerSize();
	this.DIV.style.width = windowSize.innerWidth + "px";
	this.DIV.style.height = windowSize.innerHeight + "px";

	BX.onCustomEvent(this, 'onWindowResize');
};

BX.CWindow.prototype.__expand = function()
{
	var pDocElement = BX.GetDocElement();

	if (!this.bExpanded)
	{
		var wndScroll = BX.GetWindowScrollPos(),
			wndSize = BX.GetWindowInnerSize();

		this.__expand_settings = {
			resizable: this.SETTINGS.resizable,
			draggable: this.SETTINGS.draggable,
			width: this.DIV.style.width,
			height: this.DIV.style.height,
			left: this.DIV.style.left,
			top: this.DIV.style.top,
			scrollTop: wndScroll.scrollTop,
			scrollLeft: wndScroll.scrollLeft,
			overflow: BX.style(pDocElement, 'overflow')
		};

		this.SETTINGS.resizable = false;
		this.SETTINGS.draggable = false;

		window.scrollTo(0,0);
		pDocElement.style.overflow = 'hidden';

		this.DIV.style.top = '0px';
		this.DIV.style.left = '0px';

		this.DIV.style.width = wndSize.innerWidth + 'px';
		this.DIV.style.height = wndSize.innerHeight + 'px';

		this.bExpanded = true;

		BX.onCustomEvent(this, 'onWindowExpand');
		BX.onCustomEvent(this, 'onWindowResize');

		BX.bind(window, 'resize', BX.proxy(this.__expand_onresize, this));
	}
	else
	{
		BX.unbind(window, 'resize', BX.proxy(this.__expand_onresize, this));

		this.SETTINGS.resizable = this.__expand_settings.resizable;
		this.SETTINGS.draggable = this.__expand_settings.draggable;

		pDocElement.style.overflow = this.__expand_settings.overflow;

		this.DIV.style.top = this.__expand_settings.top;
		this.DIV.style.left = this.__expand_settings.left;
		this.DIV.style.width = this.__expand_settings.width;
		this.DIV.style.height = this.__expand_settings.height;

		window.scrollTo(this.__expand_settings.scrollLeft, this.__expand_settings.scrollTop);

		this.bExpanded = false;

		BX.onCustomEvent(this, 'onWindowNarrow');
		BX.onCustomEvent(this, 'onWindowResize');

	}
};

BX.CWindow.prototype.Resize = function(x, y)
{
	var new_width = Math.max(x - this.pos.left + this.dx, this.SETTINGS.min_width);
	var new_height = Math.max(y - this.pos.top + this.dy, this.SETTINGS.min_height);

	if (this.SETTINGS.resize_restrict)
	{
		var scrollSize = BX.GetWindowScrollSize();

		if (this.pos.left + new_width > scrollSize.scrollWidth - this.dw)
			new_width = scrollSize.scrollWidth - this.pos.left - this.dw;
	}

	this.DIV.style.width = new_width + 'px';
	this.DIV.style.height = new_height + 'px';

	BX.onCustomEvent(this, 'onWindowResize');
};

BX.CWindow.prototype.__startResize = function(e)
{
	if (!this.SETTINGS.resizable)
		return false;

	if(!e) e = window.event;

	this.wndSize = BX.GetWindowScrollPos();
	this.wndSize.innerWidth = BX.GetWindowInnerSize().innerWidth;

	this.pos = BX.pos(this.DIV);

	this.x = e.clientX + this.wndSize.scrollLeft;
	this.y = e.clientY + this.wndSize.scrollTop;

	this.dx = this.pos.left + this.pos.width - this.x;
	this.dy = this.pos.top + this.pos.height - this.y;
	this.dw = this.pos.width - parseInt(this.DIV.style.width);

	BX.bind(document, "mousemove", BX.proxy(this.__moveResize, this));
	BX.bind(document, "mouseup", BX.proxy(this.__stopResize, this));

	if(document.body.setCapture)
		document.body.setCapture();

	document.onmousedown = BX.False;

	var b = document.body;
	b.ondrag = b.onselectstart = BX.False;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = 'none';
	b.style.cursor = 'se-resize';

	BX.onCustomEvent(this, 'onWindowResizeStart');

	return true;
};

BX.CWindow.prototype.__moveResize = function(e)
{
	if(!e) e = window.event;

	var windowScroll = BX.GetWindowScrollPos();

	var x = e.clientX + windowScroll.scrollLeft;
	var y = e.clientY + windowScroll.scrollTop;

	if(this.x == x && this.y == y)
		return;

	this.Resize(x, y);

	this.x = x;
	this.y = y;
};

BX.CWindow.prototype.__stopResize = function()
{
	if(document.body.releaseCapture)
		document.body.releaseCapture();

	BX.unbind(document, "mousemove", BX.proxy(this.__moveResize, this));
	BX.unbind(document, "mouseup", BX.proxy(this.__stopResize, this));

	document.onmousedown = null;

	var b = document.body;
	b.ondrag = b.onselectstart = null;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = '';
	b.style.cursor = '';

	BX.onCustomEvent(this, 'onWindowResizeFinished')
};

BX.CWindow.prototype.SetClose = function(elem)
{
	BX.bind(elem, 'click', BX.proxy(this.Close, this));
	this.ELEMENTS.close.push(elem);
};

BX.CWindow.prototype.SetDraggable = function(elem)
{
	BX.bind(elem, 'mousedown', BX.proxy(this.__startDrag, this));

	elem.style.cursor = 'move';

	this.ELEMENTS.draggable.push(elem);
	this.SETTINGS.draggable = true;
};

BX.CWindow.prototype.Move = function(x, y)
{
	var dxShadow = 1; // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	var left = parseInt(this.DIV.style.left)+x;
	var top = parseInt(this.DIV.style.top)+y;

	if (this.SETTINGS.drag_restrict)
	{
		//Left side
		if (left < 0)
			left = 0;

		//Right side
		var scrollSize = BX.GetWindowScrollSize();
		var floatWidth = this.DIV.offsetWidth;
		var floatHeight = this.DIV.offsetHeight;

		if (left > (scrollSize.scrollWidth - floatWidth - dxShadow))
			left = scrollSize.scrollWidth - floatWidth - dxShadow;

		var scrollHeight = Math.max(
			document.body.scrollHeight, document.documentElement.scrollHeight,
			document.body.offsetHeight, document.documentElement.offsetHeight,
			document.body.clientHeight, document.documentElement.clientHeight,
			scrollSize.scrollHeight
		);

		if (top > (scrollHeight - floatHeight - dxShadow))
			top = scrollHeight - floatHeight - dxShadow;

		//Top side
		if (top < 0)
			top = 0;
	}

	this.DIV.style.left = left+'px';
	this.DIV.style.top = top+'px';

	//this.AdjustShadow(div);
};

BX.CWindow.prototype.__startDrag = function(e)
{
	if (!this.SETTINGS.draggable)
		return false;

	if(!e) e = window.event;

	this.x = e.clientX + document.body.scrollLeft;
	this.y = e.clientY + document.body.scrollTop;

	this.__bWasDragged = false;
	BX.bind(document, "mousemove", BX.proxy(this.__moveDrag, this));
	BX.bind(document, "mouseup", BX.proxy(this.__stopDrag, this));

	if(document.body.setCapture)
		document.body.setCapture();

	document.onmousedown = BX.False;

	var b = document.body;
	b.ondrag = b.onselectstart = BX.False;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = 'none';
	b.style.cursor = 'move';
	return BX.PreventDefault(e);
};

BX.CWindow.prototype.__moveDrag = function(e)
{
	if(!e) e = window.event;

	var x = e.clientX + document.body.scrollLeft;
	var y = e.clientY + document.body.scrollTop;

	if(this.x == x && this.y == y)
		return;

	this.Move((x - this.x), (y - this.y));
	this.x = x;
	this.y = y;

	if (!this.__bWasDragged)
	{
		BX.onCustomEvent(this, 'onWindowDragStart');
		this.__bWasDragged = true;
		BX.bind(BX.proxy_context, "click", BX.PreventDefault);
	}

	BX.onCustomEvent(this, 'onWindowDrag');
};

BX.CWindow.prototype.__stopDrag = function(e)
{
	if(document.body.releaseCapture)
		document.body.releaseCapture();

	BX.unbind(document, "mousemove", BX.proxy(this.__moveDrag, this));
	BX.unbind(document, "mouseup", BX.proxy(this.__stopDrag, this));

	document.onmousedown = null;

	var b = document.body;
	b.ondrag = b.onselectstart = null;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = '';
	b.style.cursor = '';

	if (this.__bWasDragged)
	{
		BX.onCustomEvent(this, 'onWindowDragFinished');
		var _proxy_context = BX.proxy_context;
		setTimeout(function(){BX.unbind(_proxy_context, "click", BX.PreventDefault)}, 100);
		this.__bWasDragged = false;
	}
	return BX.PreventDefault(e);
};

BX.CWindow.prototype.DenyClose = function()
{
	this.denyClose = true;
};

BX.CWindow.prototype.AllowClose = function()
{
	this.denyClose = false;
};

BX.CWindow.prototype.ShowError = function(str)
{
	BX.onCustomEvent(this, 'onWindowError', [str]);

	if (this._wait)
		BX.closeWait(this._wait);

	window.alert(str);
};

BX.CWindow.prototype.__set_msover = function() {this.MOUSEOVER = true;};
BX.CWindow.prototype.__unset_msover = function() {this.MOUSEOVER = false;};

/* dialog window class extends window class */
BX.CWindowDialog = function() {
	var a = arguments;
	a[1] = 'dialog';
	BX.CWindowDialog.superclass.constructor.apply(this, a);

	this.DIV.style.top = '10px';
	this.OVERLAY = null;
};
BX.extend(BX.CWindowDialog, BX.CWindow);

BX.CWindowDialog.prototype.__resizeOverlay = function()
{
	var windowSize = BX.GetWindowScrollSize();
	this.OVERLAY.style.width = windowSize.scrollWidth + "px";
};

BX.CWindowDialog.prototype.CreateOverlay = function(zIndex)
{
	if (null == this.OVERLAY)
	{
		var windowSize = BX.GetWindowScrollSize();

		// scrollHeight in BX.GetWindowScrollSize may be incorrect
		var scrollHeight = Math.max(
			document.body.scrollHeight, document.documentElement.scrollHeight,
			document.body.offsetHeight, document.documentElement.offsetHeight,
			document.body.clientHeight, document.documentElement.clientHeight,
			windowSize.scrollHeight
		);

		this.OVERLAY = document.body.appendChild(BX.create("DIV", {
			style: {
				position: 'absolute',
				top: '0px',
				left: '0px',
				zIndex: zIndex || (parseInt(this.DIV.style.zIndex)-2),
				width: windowSize.scrollWidth + "px",
				height: scrollHeight + "px"
			}
		}));

		var component = BX.ZIndexManager.getComponent(this.DIV);
		if (component)
		{
			component.setOverlay(this.OVERLAY);
		}
	}

	return this.OVERLAY;
};

BX.CWindowDialog.prototype.Show = function()
{
	BX.CWindowDialog.superclass.Show.apply(this, arguments);

	this.CreateOverlay();

	this.OVERLAY.style.display = 'block';

	BX.unbind(window, 'resize', BX.proxy(this.__resizeOverlay, this));
	BX.bind(window, 'resize', BX.proxy(this.__resizeOverlay, this));
};

BX.CWindowDialog.prototype.onUnRegister = function(clean)
{
	BX.CWindowDialog.superclass.onUnRegister.apply(this, arguments);

	if (this.clean)
	{
		if (this.OVERLAY.parentNode)
			this.OVERLAY.parentNode.removeChild(this.OVERLAY);
	}
	else
	{
		this.OVERLAY.style.display = 'none';
	}

	BX.unbind(window, 'resize', BX.proxy(this.__resizeOverlay, this));
};

/* standard bitrix dialog extends BX.CWindowDialog */
/*
	arParams = {
		(
			title: 'dialog title',
			head: 'head block html',
			content: 'dialog content',
			icon: 'head icon classname or filename',

			resize_id: 'some id to save resize information'// useless if resizable = false
		)
		or
		(
			content_url: url to content load
				loaded content scripts can use BX.WindowManager.Get() to get access to the current window object
		)

		height: window_height_in_pixels,
		width: window_width_in_pixels,

		draggable: true|false,
		resizable: true|false,

		min_height: min_window_height_in_pixels, // useless if resizable = false
		min_width: min_window_width_in_pixels, // useless if resizable = false

		buttons: [
			'html_code',
			BX.CDialog.btnSave, BX.CDialog.btnCancel, BX.CDialog.btnClose
		]
	}
*/
BX.CDialog = function(arParams)
{
	BX.CDialog.superclass.constructor.apply(this);

	this._sender = 'core_window_cdialog';

	this.PARAMS = arParams || {};

	for (var i in this.defaultParams)
	{
		if (typeof this.PARAMS[i] == 'undefined')
			this.PARAMS[i] = this.defaultParams[i];
	}

	this.PARAMS.width = (!isNaN(parseInt(this.PARAMS.width)))
		? this.PARAMS.width
		: this.defaultParams['width'];
	this.PARAMS.height = (!isNaN(parseInt(this.PARAMS.height)))
		? this.PARAMS.height
		: this.defaultParams['height'];

	if (this.PARAMS.resize_id || this.PARAMS.content_url)
	{
		var arSize = BX.WindowManager.getRuntimeWindowSize(this.PARAMS.resize_id || this.PARAMS.content_url);
		if (arSize)
		{
			this.PARAMS.width = arSize.width;
			this.PARAMS.height = arSize.height;
		}
	}

	BX.addClass(this.DIV, 'bx-core-adm-dialog');
	this.DIV.id = 'bx-admin-prefix';

	this.PARTS = {};

	this.DIV.style.height = null;
	this.DIV.style.width = null;

	this.PARTS.TITLEBAR = this.DIV.appendChild(BX.create('DIV', {props: {
			className: 'bx-core-adm-dialog-head'
		}
	}));

	this.PARTS.TITLE_CONTAINER = this.PARTS.TITLEBAR.appendChild(BX.create('SPAN', {
		props: {className: 'bx-core-adm-dialog-head-inner'},
		text: this.PARAMS.title
	}));

	this.PARTS.TITLEBAR_ICONS = this.PARTS.TITLEBAR.appendChild(BX.create('DIV', {
		props: {
			className: 'bx-core-adm-dialog-head-icons'
		},
		children: (this.PARAMS.resizable ? [
			BX.create('SPAN', {props: {className: 'bx-core-adm-icon-expand', title: BX.message('JS_CORE_WINDOW_EXPAND')}}),
			BX.create('SPAN', {props: {className: 'bx-core-adm-icon-close', title: BX.message('JS_CORE_WINDOW_CLOSE')}})
		] : [
			BX.create('SPAN', {props: {className: 'bx-core-adm-icon-close', title: BX.message('JS_CORE_WINDOW_CLOSE')}})
		])
	}));


	this.PARTS.CONTENT = this.DIV.appendChild(BX.create('DIV', {
		props: {className: 'bx-core-adm-dialog-content-wrap adm-workarea'}
	}));

	this.PARTS.CONTENT_DATA = this.PARTS.CONTENT.appendChild(BX.create('DIV', {
		props: {className: 'bx-core-adm-dialog-content'},
		style: {
			height: this.PARAMS.height + 'px',
			width: this.PARAMS.width + 'px'
		}
	}));

	this.PARTS.HEAD = this.PARTS.CONTENT_DATA.appendChild(BX.create('DIV', {
		props: {
			className: 'bx-core-adm-dialog-head-block' + (this.PARAMS.icon ? ' ' + this.PARAMS.icon : '')
		}
	}));

	this.SetHead(this.PARAMS.head);
	this.SetContent(this.PARAMS.content);
	this.SetTitle(this.PARAMS.title);
	this.SetClose(this.PARTS.TITLEBAR_ICONS.lastChild);

	if (this.PARAMS.resizable)
	{
		this.SetExpand(this.PARTS.TITLEBAR_ICONS.firstChild);
		this.SetExpand(this.PARTS.TITLEBAR, 'dblclick');

		BX.addCustomEvent(this, 'onWindowExpand', BX.proxy(this.__onexpand, this));
		BX.addCustomEvent(this, 'onWindowNarrow', BX.proxy(this.__onexpand, this));
	}

	this.PARTS.FOOT = this.PARTS.BUTTONS_CONTAINER = this.PARTS.CONTENT.appendChild(BX.create('DIV', {
			props: {
				className: 'bx-core-adm-dialog-buttons'
			},
			//events: {
			//	'click': BX.delegateEvent({property:{type: /button|submit/}}, BX.delegate(function() {this.showWait(BX.proxy_context)}, this))
			//},
			children: this.ShowButtons()
		}
	));

	if (this.PARAMS.draggable)
		this.SetDraggable(this.PARTS.TITLEBAR);

	if (this.PARAMS.resizable)
	{
		this.PARTS.RESIZER = this.DIV.appendChild(BX.create('DIV', {
			props: {className: 'bx-core-resizer'}
		}));

		this.SetResize(this.PARTS.RESIZER);

		this.SETTINGS.min_width = this.PARAMS.min_width;
		this.SETTINGS.min_height = this.PARAMS.min_height;
	}

	this.auth_callback = BX.delegate(function(){
		this.PARAMS.content = '';
		this.hideNotify();
		this.Show();
	}, this)
};
BX.extend(BX.CDialog, BX.CWindowDialog);

BX.CDialog.prototype.defaultParams = {
	width: 700,
	height: 400,
	min_width: 500,
	min_height: 300,

	resizable: true,
	draggable: true,

	title: '',
	icon: ''
};

BX.CDialog.prototype.showWait = function(el)
{
	if (BX.type.isElementNode(el) && (el.type == 'button' || el.type == 'submit'))
	{
		BX.defer(function(){el.disabled = true})();

		var bSave = (BX.hasClass(el, 'adm-btn-save') || BX.hasClass(el, 'adm-btn-save')),
			pos = BX.pos(el, true);

		el.bxwaiter = this.PARTS.FOOT.appendChild(BX.create('DIV', {
			props: {className: 'adm-btn-load-img' + (bSave ? '-green' : '')},
			style: {
				top: parseInt((pos.bottom + pos.top)/2 - 10) + 'px',
				left: parseInt((pos.right + pos.left)/2 - 10) + 'px'
			}
		}));

		BX.addClass(el, 'adm-btn-load');

		this.lastWaitElement = el;

		return el.bxwaiter;
	}
	return null;
};

BX.CDialog.prototype.closeWait = function(el)
{
	el = el || this.lastWaitElement;

	if (BX.type.isElementNode(el))
	{
		if (el.bxwaiter)
		{
			if(el.bxwaiter.parentNode)
			{
				el.bxwaiter.parentNode.removeChild(el.bxwaiter);
			}

			el.bxwaiter = null;
		}

		el.disabled = false;
		BX.removeClass(el, 'adm-btn-load');

		if (this.lastWaitElement == el)
			this.lastWaitElement = null;
	}
};

BX.CDialog.prototype.Authorize = function(arAuthResult)
{
	this.bSkipReplaceContent = true;
	this.ShowError(BX.message('JSADM_AUTH_REQ'));

	BX.onCustomEvent(this, 'onWindowError', []);

	BX.closeWait();

	(new BX.CAuthDialog({
		content_url: this.PARAMS.content_url,
		auth_result: arAuthResult,
		callback: BX.delegate(function(){
			if (this.auth_callback)
				this.auth_callback()
		}, this)
	})).Show();
};

BX.CDialog.prototype.ShowError = function(str)
{
	BX.onCustomEvent(this, 'onWindowError', [str]);

	this.closeWait();

	if (this._wait)
		BX.closeWait(this._wait);

	this.Notify(str, true);
};


BX.CDialog.prototype.__expandGetSize = function()
{
	var pDocElement = BX.GetDocElement();
	pDocElement.style.overflow = 'hidden';

	var wndSize = BX.GetWindowInnerSize();

	pDocElement.scrollTop = 0;

	this.DIV.style.top = '-' + this.dxShadow + 'px';
	this.DIV.style.left = '-' + this.dxShadow + 'px';

	return {
		width: (wndSize.innerWidth - parseInt(BX.style(this.PARTS.CONTENT, 'padding-right')) - parseInt(BX.style(this.PARTS.CONTENT, 'padding-left'))) + this.dxShadow,
		height: (wndSize.innerHeight - this.PARTS.TITLEBAR.offsetHeight - this.PARTS.FOOT.offsetHeight - parseInt(BX.style(this.PARTS.CONTENT, 'padding-top')) - parseInt(BX.style(this.PARTS.CONTENT, 'padding-bottom'))) + this.dxShadow
	};
};

BX.CDialog.prototype.__expand = function()
{
	var pDocElement = BX.GetDocElement();
	this.dxShadow = 2;

	if (!this.bExpanded)
	{
		var wndScroll = BX.GetWindowScrollPos();

		this.__expand_settings = {
			resizable: this.SETTINGS.resizable,
			draggable: this.SETTINGS.draggable,
			width: this.PARTS.CONTENT_DATA.style.width,
			height: this.PARTS.CONTENT_DATA.style.height,
			left: this.DIV.style.left,
			top: this.DIV.style.top,
			scrollTop: wndScroll.scrollTop,
			scrollLeft: wndScroll.scrollLeft,
			overflow: BX.style(pDocElement, 'overflow')
		};

		this.SETTINGS.resizable = false;
		this.SETTINGS.draggable = false;

		var pos = this.__expandGetSize();

		this.PARTS.CONTENT_DATA.style.width = pos.width + 'px';
		this.PARTS.CONTENT_DATA.style.height = pos.height + 'px';

		window.scrollTo(0,0);
		pDocElement.style.overflow = 'hidden';

		this.bExpanded = true;

		BX.onCustomEvent(this, 'onWindowExpand');
		BX.onCustomEvent(this, 'onWindowResize');
		BX.onCustomEvent(this, 'onWindowResizeExt', [{'width': pos.width, 'height': pos.height}]);

		BX.bind(window, 'resize', BX.proxy(this.__expand_onresize, this));
	}
	else
	{
		BX.unbind(window, 'resize', BX.proxy(this.__expand_onresize, this));

		this.SETTINGS.resizable = this.__expand_settings.resizable;
		this.SETTINGS.draggable = this.__expand_settings.draggable;

		pDocElement.style.overflow = this.__expand_settings.overflow;

		this.DIV.style.top = this.__expand_settings.top;
		this.DIV.style.left = this.__expand_settings.left;
		this.PARTS.CONTENT_DATA.style.width = this.__expand_settings.width;
		this.PARTS.CONTENT_DATA.style.height = this.__expand_settings.height;
		window.scrollTo(this.__expand_settings.scrollLeft, this.__expand_settings.scrollTop);
		this.bExpanded = false;

		BX.onCustomEvent(this, 'onWindowNarrow');
		BX.onCustomEvent(this, 'onWindowResize');
		BX.onCustomEvent(this, 'onWindowResizeExt', [{'width': parseInt(this.__expand_settings.width), 'height': parseInt(this.__expand_settings.height)}]);
	}
};

BX.CDialog.prototype.__expand_onresize = function()
{
	var pos = this.__expandGetSize();

	this.PARTS.CONTENT_DATA.style.width = pos.width + 'px';
	this.PARTS.CONTENT_DATA.style.height = pos.height + 'px';

	BX.onCustomEvent(this, 'onWindowResize');
	BX.onCustomEvent(this, 'onWindowResizeExt', [pos]);
};

BX.CDialog.prototype.__onexpand = function()
{
	var ob = this.PARTS.TITLEBAR_ICONS.firstChild;
	ob.className = BX.toggle(ob.className, ['bx-core-adm-icon-expand', 'bx-core-adm-icon-narrow']);
	ob.title = BX.toggle(ob.title, [BX.message('JS_CORE_WINDOW_EXPAND'), BX.message('JS_CORE_WINDOW_NARROW')]);

	if (this.PARTS.RESIZER)
	{
		this.PARTS.RESIZER.style.display = this.bExpanded ? 'none' : 'block';
	}
};


BX.CDialog.prototype.__startResize = function(e)
{
	if (!this.SETTINGS.resizable)
		return false;

	if(!e) e = window.event;

	this.wndSize = BX.GetWindowScrollPos();
	this.wndSize.innerWidth = BX.GetWindowInnerSize().innerWidth;

	this.pos = BX.pos(this.PARTS.CONTENT_DATA);

	this.x = e.clientX + this.wndSize.scrollLeft;
	this.y = e.clientY + this.wndSize.scrollTop;

	this.dx = this.pos.left + this.pos.width - this.x;
	this.dy = this.pos.top + this.pos.height - this.y;


	// TODO: suspicious
	this.dw = this.pos.width - parseInt(this.PARTS.CONTENT_DATA.style.width) + parseInt(BX.style(this.PARTS.CONTENT, 'padding-right'));

	BX.bind(document, "mousemove", BX.proxy(this.__moveResize, this));
	BX.bind(document, "mouseup", BX.proxy(this.__stopResize, this));

	if(document.body.setCapture)
		document.body.setCapture();

	document.onmousedown = BX.False;

	var b = document.body;
	b.ondrag = b.onselectstart = BX.False;
	b.style.MozUserSelect = this.DIV.style.MozUserSelect = 'none';
	b.style.cursor = 'se-resize';

	BX.onCustomEvent(this, 'onWindowResizeStart');

	return true;
};

BX.CDialog.prototype.Resize = function(x, y)
{
	var new_width = Math.max(x - this.pos.left + this.dx, this.SETTINGS.min_width);
	var new_height = Math.max(y - this.pos.top + this.dy, this.SETTINGS.min_height);

	if (this.SETTINGS.resize_restrict)
	{
		var scrollSize = BX.GetWindowScrollSize();

		if (this.pos.left + new_width > scrollSize.scrollWidth - this.dw)
			new_width = scrollSize.scrollWidth - this.pos.left - this.dw;
	}

	this.PARTS.CONTENT_DATA.style.width = new_width + 'px';
	this.PARTS.CONTENT_DATA.style.height = new_height + 'px';

	BX.onCustomEvent(this, 'onWindowResize');
	BX.onCustomEvent(this, 'onWindowResizeExt', [{'height': new_height, 'width': new_width}]);
};

BX.CDialog.prototype.SetSize = function(obSize)
{
	this.PARTS.CONTENT_DATA.style.width = obSize.width + 'px';
	this.PARTS.CONTENT_DATA.style.height = obSize.height + 'px';

	BX.onCustomEvent(this, 'onWindowResize');
	BX.onCustomEvent(this, 'onWindowResizeExt', [obSize]);
};

BX.CDialog.prototype.GetParameters = function(form_name)
{
	var form = this.GetForm();

	if(!form)
		return "";

	var i, s = "";
	var n = form.elements.length;

	var delim = '';
	for(i=0; i<n; i++)
	{
		if (s != '') delim = '&';
		var el = form.elements[i];
		if (el.disabled)
			continue;

		switch(el.type.toLowerCase())
		{
			case 'text':
			case 'textarea':
			case 'password':
			case 'hidden':
				if (null == form_name && el.name.substr(el.name.length-4) == '_alt' && form.elements[el.name.substr(0, el.name.length-4)])
					break;
				s += delim + el.name + '=' + BX.util.urlencode(el.value);
				break;
			case 'radio':
				if(el.checked)
					s += delim + el.name + '=' + BX.util.urlencode(el.value);
				break;
			case 'checkbox':
				s += delim + el.name + '=' + BX.util.urlencode(el.checked ? 'Y':'N');
				break;
			case 'select-one':
				var val = "";
				if (null == form_name && form.elements[el.name + '_alt'] && el.selectedIndex == 0)
					val = form.elements[el.name+'_alt'].value;
				else
					val = el.value;
				s += delim + el.name + '=' + BX.util.urlencode(val);
				break;
			case 'select-multiple':
				var j, bAdded = false;
				var l = el.options.length;
				for (j=0; j<l; j++)
				{
					if (el.options[j].selected)
					{
						s += delim + el.name + '=' + BX.util.urlencode(el.options[j].value);
						bAdded = true;
					}
				}
				if (!bAdded)
					s += delim + el.name + '=';
				break;
			default:
				break;
		}
	}

	return s;
};

BX.CDialog.prototype.PostParameters = function(params)
{
	var url = this.PARAMS.content_url;

	if (null == params)
		params = "";

	params += (params == "" ? "" : "&") + "bxsender=" + this._sender;

	var index = url.indexOf('?');
	if (index == -1)
		url += '?' + params;
	else
		url = url.substring(0, index) + '?' + params + "&" + url.substring(index+1);

	BX.showWait();

	this.auth_callback = BX.delegate(function(){
		this.hideNotify();
		this.PostParameters(params);
	}, this);

	BX.ajax.Setup({skipAuthCheck:true},true);
	BX.ajax.post(url, this.GetParameters(), BX.delegate(function(result) {
		BX.closeWait();
		if (!this.bSkipReplaceContent)
		{
			this.ClearButtons(); // buttons are appended during form reload, so we should clear footer
			this.SetContent(result);
			this.Show(true);
		}

		this.bSkipReplaceContent = false;
	}, this));
};

BX.CDialog.prototype.Submit = function(params, url)
{
	var FORM = this.GetForm();
	if (FORM)
	{
		FORM.onsubmit = null;

		FORM.method = 'POST';
		if (!FORM.action || url)
		{
			url = url || this.PARAMS.content_url;
			if (null != params)
			{
				var index = url.indexOf('?');
				if (index == -1)
					url += '?' + params;
				else
					url = url.substring(0, index) + '?' + params + "&" + url.substring(index+1);
			}

			FORM.action = url;
		}

		if (!FORM._bxsender)
		{
			FORM._bxsender = FORM.appendChild(BX.create('INPUT', {
				attrs: {
					type: 'hidden',
					name: 'bxsender',
					value: this._sender
				}
			}));
		}

		this._wait = BX.showWait();

		this.auth_callback = BX.delegate(function(){
			this.hideNotify();
			this.Submit(params);
		}, this);

		BX.ajax.submit(FORM, BX.delegate(function(){this.closeWait()}, this));
	}
	else
	{
		window.alert('no form registered!');
	}
};

BX.CDialog.prototype.GetForm = function()
{
	if (null == this.__form)
	{
		var forms = this.PARTS.CONTENT_DATA.getElementsByTagName('FORM');
		this.__form = forms[0] ? forms[0] : null;
	}

	return this.__form;
};

BX.CDialog.prototype.GetRealForm = function()
{
	if (null == this.__rform)
	{
		var forms = this.PARTS.CONTENT_DATA.getElementsByTagName('FORM');
		this.__rform = forms[1] ? forms[1] : (forms[0] ? forms[0] : null);
	}

	return this.__rform;
};

BX.CDialog.prototype._checkButton = function(btn)
{
	var arCustomButtons = ['btnSave', 'btnCancel', 'btnClose'];

	for (var i = 0; i < arCustomButtons.length; i++)
	{
		if (this[arCustomButtons[i]] && (btn == this[arCustomButtons[i]]))
			return arCustomButtons[i];
	}

	return false;
};

BX.CDialog.prototype.ShowButtons = function()
{
	var result = [];
	if (this.PARAMS.buttons)
	{
		if (this.PARAMS.buttons.title) this.PARAMS.buttons = [this.PARAMS.buttons];

		for (var i=0, len=this.PARAMS.buttons.length; i<len; i++)
		{
			if (BX.type.isNotEmptyString(this.PARAMS.buttons[i]))
			{
				result.push(this.PARAMS.buttons[i]);
			}
			else if (BX.type.isElementNode(this.PARAMS.buttons[i]))
			{
				result.push(this.PARAMS.buttons[i]);
			}
			else if (this.PARAMS.buttons[i])
			{
				//if (!(this.PARAMS.buttons[i] instanceof BX.CWindowButton))
				if (!BX.is_subclass_of(this.PARAMS.buttons[i], BX.CWindowButton))
				{
					var b = this._checkButton(this.PARAMS.buttons[i]); // hack to set links to real CWindowButton object in btnSave etc;
					this.PARAMS.buttons[i] = new BX.CWindowButton(this.PARAMS.buttons[i]);
					if (b) this[b] = this.PARAMS.buttons[i];
				}

				result.push(this.PARAMS.buttons[i].Button(this));
			}
		}
	}

	return result;
};

BX.CDialog.prototype.setAutosave = function () {
	if (!this.bSetAutosaveDelay)
	{
		this.bSetAutosaveDelay = true;
		setTimeout(BX.proxy(this.setAutosave, this), 10);
	}
};

BX.CDialog.prototype.SetTitle = function(title)
{
	this.PARAMS.title = title;
	BX.cleanNode(this.PARTS.TITLE_CONTAINER).appendChild(document.createTextNode(this.PARAMS.title));
};

BX.CDialog.prototype.SetHead = function(head)
{
	this.PARAMS.head = BX.util.trim(head);
	this.PARTS.HEAD.innerHTML = this.PARAMS.head || "&nbsp;";
	this.PARTS.HEAD.style.display = this.PARAMS.head ? 'block' : 'none';
	this.adjustSize();
};

BX.CDialog.prototype.Notify = function(note, bError, html)
{
	if (!this.PARTS.NOTIFY)
	{
		this.PARTS.NOTIFY = this.DIV.insertBefore(BX.create('DIV', {
			props: {className: 'adm-warning-block'},
			children: [
				BX.create('SPAN', {
					props: {className: 'adm-warning-text'}
				}),
				BX.create('SPAN', {
					props: {className: 'adm-warning-icon'}
				}),
				BX.create('SPAN', {
					props: {className: 'adm-warning-close'},
					events: {click: BX.proxy(this.hideNotify, this)}
				})
			]
		}), this.DIV.firstChild);
	}

	if (bError)
		BX.addClass(this.PARTS.NOTIFY, 'adm-warning-block-red');
	else
		BX.removeClass(this.PARTS.NOTIFY, 'adm-warning-block-red');

	if(html !== true)
	{
		note = BX.util.htmlspecialchars(note);
	}

	this.PARTS.NOTIFY.firstChild.innerHTML = note || '&nbsp;';
	this.PARTS.NOTIFY.firstChild.style.width = (this.PARAMS.width-50) + 'px';
	BX.removeClass(this.PARTS.NOTIFY, 'adm-warning-animate');
};

BX.CDialog.prototype.hideNotify = function()
{
	BX.addClass(this.PARTS.NOTIFY, 'adm-warning-animate');
};

BX.CDialog.prototype.__adjustHeadToIcon = function()
{
	if (!this.PARTS.HEAD.offsetHeight)
	{
		setTimeout(BX.delegate(this.__adjustHeadToIcon, this), 50);
	}
	else
	{
		if (this.icon_image && this.icon_image.height && this.icon_image.height > this.PARTS.HEAD.offsetHeight - 5)
		{
			this.PARTS.HEAD.style.height = this.icon_image.height + 5 + 'px';
			this.adjustSize();
		}

		this.icon_image.onload = null;
		this.icon_image = null;
	}
};

BX.CDialog.prototype.SetIcon = function(icon_class)
{
	if (this.PARAMS.icon != icon_class)
	{
		if (this.PARAMS.icon)
			BX.removeClass(this.PARTS.HEAD, this.PARAMS.icon);

		this.PARAMS.icon = icon_class;

		if (this.PARAMS.icon)
		{
			BX.addClass(this.PARTS.HEAD, this.PARAMS.icon);

			var icon_file = (BX.style(this.PARTS.HEAD, 'background-image') || BX.style(this.PARTS.HEAD, 'backgroundImage'));
			if (BX.type.isNotEmptyString(icon_file) && icon_file != 'none')
			{
				var match = icon_file.match(new RegExp('url\\s*\\(\\s*(\'|"|)(.+?)(\\1)\\s*\\)'));
				if(match)
				{
					icon_file = match[2];
					if (BX.type.isNotEmptyString(icon_file))
					{
						this.icon_image = new Image();
						this.icon_image.onload = BX.delegate(this.__adjustHeadToIcon, this);
						this.icon_image.src = icon_file;
					}
				}
			}
		}
	}
	this.adjustSize();
};

BX.CDialog.prototype.SetIconFile = function(icon_file)
{
	this.icon_image = new Image();
	this.icon_image.onload = BX.delegate(this.__adjustHeadToIcon, this);
	this.icon_image.src = icon_file;

	BX.adjust(this.PARTS.HEAD, {style: {backgroundImage: 'url(' + icon_file + ')', backgroundPosition: 'right 9px'/*'99% center'*/}});
	this.adjustSize();
};

/*
BUTTON: {
	title: 'title',
	'action': function executed in window object context
}
BX.CDialog.btnSave || BX.CDialog.btnCancel - standard buttons
*/

BX.CDialog.prototype.SetButtons = function(a)
{
	if (BX.type.isString(a))
	{
		if (a.length > 0)
		{
			this.PARTS.BUTTONS_CONTAINER.innerHTML += a;

			var btns = this.PARTS.BUTTONS_CONTAINER.getElementsByTagName('INPUT');
			if (btns.length > 0)
			{
				this.PARAMS.buttons = [];
				for (var i = 0; i < btns.length; i++)
				{
					this.PARAMS.buttons.push(new BX.CWindowButton({btn: btns[i], parentWindow: this}));
				}
			}
		}
	}
	else
	{
		this.PARAMS.buttons = a;
		BX.adjust(this.PARTS.BUTTONS_CONTAINER, {
			children: this.ShowButtons()
		});
	}
	this.adjustSize();
};

BX.CDialog.prototype.ClearButtons = function()
{
	BX.cleanNode(this.PARTS.BUTTONS_CONTAINER);
	this.adjustSize();
};

BX.CDialog.prototype.SetContent = function(html)
{
	this.__form = null;

	if (BX.type.isElementNode(html))
	{
		if (html.parentNode)
			html.parentNode.removeChild(html);
	}
	else if (BX.type.isString(html))
	{
		html = BX.create('DIV', {html: html});
	}

	this.PARAMS.content = html;
	BX.cleanNode(this.PARTS.CONTENT_DATA);

	BX.adjust(this.PARTS.CONTENT_DATA, {
		children: [
			this.PARTS.HEAD,
			BX.create('DIV', {
				props: {
					className: 'bx-core-adm-dialog-content-wrap-inner'
				},
				children: [this.PARAMS.content]
			})
		]
	});

	if (this.PARAMS.content_url && this.GetForm())
	{
		this.__form.submitbtn = this.__form.appendChild(BX.create('INPUT', {props:{type:'submit'},style:{display:'none'}}));
		this.__form.onsubmit = BX.delegate(this.__submit, this);
	}
};

BX.CDialog.prototype.__submit = function(e)
{
	for (var i=0,len=this.PARAMS.buttons.length; i<len; i++)
	{
		if (
			this.PARAMS.buttons[i]
			&& (
				this.PARAMS.buttons[i].name && /save|apply/i.test(this.PARAMS.buttons[i].name)
				||
				this.PARAMS.buttons[i].btn && this.PARAMS.buttons[i].btn.name && /save|apply/i.test(this.PARAMS.buttons[i].btn.name)
			)
		)
		{
			this.PARAMS.buttons[i].emulate();
			break;
		}
	}

	return BX.PreventDefault(e);
};

BX.CDialog.prototype.SwapContent = function(cont)
{
	cont = BX(cont);

	BX.cleanNode(this.PARTS.CONTENT_DATA);
	cont.parentNode.removeChild(cont);
	this.PARTS.CONTENT_DATA.appendChild(cont);
	cont.style.display = 'block';
	this.SetContent(cont.innerHTML);
};

// this method deprecated
BX.CDialog.prototype.adjustSize = function()
{
};

// this method deprecated
BX.CDialog.prototype.__adjustSize = function()
{
};

BX.CDialog.prototype.adjustSizeEx = function()
{
	BX.defer(this.__adjustSizeEx, this)();
};

BX.CDialog.prototype.__adjustSizeEx = function()
{
	var ob = this.PARTS.CONTENT_DATA.firstChild,
		new_height = 0,
		marginTop,
		marginBottom;

	while (ob)
	{
		if (BX.type.isElementNode(ob))
		{
			marginTop = parseInt(BX.style(ob, 'margin-top'), 10);
			if (isNaN(marginTop))
				marginTop = 0;
			marginBottom = parseInt(BX.style(ob, 'margin-bottom'), 10);
			if (isNaN(marginBottom))
				marginBottom = 0;
			new_height += ob.offsetHeight + marginTop + marginBottom;
		}
		ob = BX.nextSibling(ob);
	}

	if (new_height)
		this.PARTS.CONTENT_DATA.style.height = new_height + 'px';
};


BX.CDialog.prototype.__onResizeFinished = function()
{
	BX.WindowManager.saveWindowSize(
		this.PARAMS.resize_id || this.PARAMS.content_url, {height: parseInt(this.PARTS.CONTENT_DATA.style.height), width: parseInt(this.PARTS.CONTENT_DATA.style.width)}
	);
};

BX.CDialog.prototype.Show = function(bNotRegister)
{
	if ((!this.PARAMS.content) && this.PARAMS.content_url && BX.ajax && !bNotRegister)
	{
		var wait = BX.showWait();

		BX.WindowManager.currently_loaded = this;

		this.CreateOverlay();
		this.OVERLAY.style.display = 'block';
		this.OVERLAY.className = 'bx-core-dialog-overlay';

		var post_data = '', method = 'GET';
		if (this.PARAMS.content_post)
		{
			post_data = this.PARAMS.content_post;
			method = 'POST';
		}

		var url = this.PARAMS.content_url
			+ (this.PARAMS.content_url.indexOf('?')<0?'?':'&')+'bxsender=' + this._sender;

		this.auth_callback = BX.delegate(function(){
			this.PARAMS.content = '';
			this.hideNotify();
			this.Show();
		}, this);

		BX.ajax({
			method: method,
			dataType: 'html',
			url: url,
			data: post_data,
			skipAuthCheck: true,
			onsuccess: BX.delegate(function(data) {
				BX.closeWait(null, wait);

				this.SetContent(data || '&nbsp;');
				this.Show();
			}, this)
		});
	}
	else
	{
		BX.WindowManager.currently_loaded = null;
		BX.CDialog.superclass.Show.apply(this, arguments);

		this.adjustPos();

		this.OVERLAY.className = 'bx-core-dialog-overlay';

		this.__adjustSize();

		BX.removeCustomEvent(this, 'onWindowResize', BX.proxy(this.__adjustSize, this));
		BX.addCustomEvent(this, 'onWindowResize', BX.proxy(this.__adjustSize, this));

		if (this.PARAMS.resizable && (this.PARAMS.content_url || this.PARAMS.resize_id))
		{
			BX.removeCustomEvent(this, 'onWindowResizeFinished', BX.proxy(this.__onResizeFinished, this));
			BX.addCustomEvent(this, 'onWindowResizeFinished', BX.proxy(this.__onResizeFinished, this));
		}
	}
};

BX.CDialog.prototype.GetInnerPos = function()
{
	return {'width': parseInt(this.PARTS.CONTENT_DATA.style.width), 'height': parseInt(this.PARTS.CONTENT_DATA.style.height)};
};

BX.CDialog.prototype.adjustPos = function()
{
	if (!this.bExpanded)
	{
		var currentWindow = window;
		var topWindow = BX.PageObject.getRootWindow();
		if (topWindow.BX.SidePanel && topWindow.BX.SidePanel.Instance && topWindow.BX.SidePanel.Instance.getTopSlider())
		{
			currentWindow = topWindow.BX.SidePanel.Instance.getTopSlider().getWindow();
		}
		var windowSize = currentWindow.BX.GetWindowInnerSize();
		var windowScroll = currentWindow.BX.GetWindowScrollPos();

		var style = {
			left: parseInt(windowScroll.scrollLeft + windowSize.innerWidth / 2 - parseInt(this.DIV.offsetWidth) / 2) + 'px',
			top: Math.max(parseInt(windowScroll.scrollTop + windowSize.innerHeight / 2 - parseInt(this.DIV.offsetHeight) / 2), 0) + 'px'
		};

		BX.adjust(this.DIV, {
			style: style
		});
	}
};

BX.CDialog.prototype.GetContent = function () {return this.PARTS.CONTENT_DATA};

BX.CDialog.prototype.btnSave = BX.CDialog.btnSave = {
	title: BX.message('JS_CORE_WINDOW_SAVE'),
	id: 'savebtn',
	name: 'savebtn',
	className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
	action: function () {
		this.disableUntilError();
		this.parentWindow.PostParameters();
	}
};

BX.CDialog.prototype.btnCancel = BX.CDialog.btnCancel = {
	title: BX.message('JS_CORE_WINDOW_CANCEL'),
	id: 'cancel',
	name: 'cancel',
	action: function () {
		this.parentWindow.Close();
	}
};

BX.CDialog.prototype.btnClose = BX.CDialog.btnClose = {
	title: BX.message('JS_CORE_WINDOW_CLOSE'),
	id: 'close',
	name: 'close',
	action: function () {
		this.parentWindow.Close();
	}
};

/* special child for admin forms loaded into public page */
BX.CAdminDialog = function(arParams)
{
	BX.CAdminDialog.superclass.constructor.apply(this, arguments);

	this._sender = 'core_window_cadmindialog';

	BX.addClass(this.DIV, 'bx-core-adm-admin-dialog');

	this.PARTS.CONTENT.insertBefore(this.PARTS.HEAD, this.PARTS.CONTENT.firstChild);
	this.PARTS.HEAD.className = 'bx-core-adm-dialog-tabs';
};
BX.extend(BX.CAdminDialog, BX.CDialog);

BX.CAdminDialog.prototype.SetHead = function()
{
	BX.CAdminDialog.superclass.SetHead.apply(this, arguments);

	if (this.PARTS.HEAD.firstChild && BX.type.isElementNode(this.PARTS.HEAD.firstChild))
	{
		var ob = this.PARTS.HEAD.firstChild, new_width = 0, marginLeft = 0, marginRight = 0;

		while (ob)
		{
			if (BX.type.isElementNode(ob))
			{
				marginLeft = parseInt(BX.style(ob, 'margin-left'), 10);
				if (isNaN(marginLeft))
					marginLeft = 0;
				marginRight = parseInt(BX.style(ob, 'margin-right'), 10);
				if (isNaN(marginRight))
					marginRight = 0;
				new_width += ob.offsetWidth + marginLeft + marginRight;
			}
			ob = BX.nextSibling(ob);
		}

		this.SETTINGS.min_width = Math.max(new_width, this.SETTINGS.min_width) - 2;
		if (this.PARAMS.width < this.SETTINGS.min_width)
		{
			BX.adjust(this.PARTS.CONTENT_DATA, {
				style: {
					width: this.SETTINGS.min_width + 'px'
				}
			});
		}
	}
};

BX.CAdminDialog.prototype.SetContent = function(html)
{
	this.__form = null;

	if (BX.type.isElementNode(html))
	{
		if (html.parentNode)
			html.parentNode.removeChild(html);
	}

	this.PARAMS.content = html;
	BX.cleanNode(this.PARTS.CONTENT_DATA);

	BX.adjust(this.PARTS.CONTENT_DATA, {
		children: [
			this.PARAMS.content || '&nbsp;'
		]
	});

	if (this.PARAMS.content_url && this.GetForm())
	{
		this.__form.appendChild(BX.create('INPUT', {props:{type:'submit'},style:{display:'none'}}));
		this.__form.onsubmit = BX.delegate(this.__submit, this);
	}
};

BX.CAdminDialog.prototype.__expandGetSize = function()
{
	var res = BX.CAdminDialog.superclass.__expandGetSize.apply(this, arguments);

	res.width -= parseInt(BX.style(this.PARTS.CONTENT_DATA, 'padding-right')) + parseInt(BX.style(this.PARTS.CONTENT_DATA, 'padding-left'));
	res.height -= parseInt(BX.style(this.PARTS.CONTENT_DATA, 'padding-top')) + parseInt(BX.style(this.PARTS.CONTENT_DATA, 'padding-bottom'));

	res.height -= this.PARTS.HEAD.offsetHeight;

	return res;
};

BX.CAdminDialog.prototype.Submit = function()
{
	var FORM = this.GetForm();
	if (FORM && !FORM['bxpublic'] && !/bxpublic=/.test(FORM.action))
	{
		FORM.appendChild(BX.create('INPUT', {
			props: {
				type: 'hidden',
				name: 'bxpublic',
				value: 'Y'
			}
		}));
	}

	return BX.CAdminDialog.superclass.Submit.apply(this, arguments);
};

BX.CAdminDialog.prototype.btnSave = BX.CAdminDialog.btnSave = {
	title: BX.message('JS_CORE_WINDOW_SAVE'),
	id: 'savebtn',
	name: 'savebtn',
	className: 'adm-btn-save',
	action: function () {
		this.disableUntilError();
		this.parentWindow.Submit();
	}
};

BX.CAdminDialog.btnCancel = BX.CAdminDialog.superclass.btnCancel;
BX.CAdminDialog.btnClose = BX.CAdminDialog.superclass.btnClose;

BX.CDebugDialog = function(arParams)
{
	BX.CDebugDialog.superclass.constructor.apply(this, arguments);
};
BX.extend(BX.CDebugDialog, BX.CDialog);

BX.CDebugDialog.prototype.ShowDetails = function(div_id)
{
	var div = BX(div_id);
	if (div)
	{
		if (this.div_detail_current)
			this.div_detail_current.style.display = 'none';

		div.style.display = 'block';
		this.div_detail_current = div;
	}
};

BX.CDebugDialog.prototype.SetContent = function(html)
{
	if (!html)
		return;

	var arHtml = html.split('#DIVIDER#');
	if (arHtml.length > 1)
	{
		this.PARAMS.content = arHtml[1];

		this.PARTS.CONTENT_DATA.style.overflow = 'hidden';

		BX.CDebugDialog.superclass.SetContent.apply(this, [arHtml[1]]);

		this.PARTS.CONTENT_INNER = this.PARTS.CONTENT_DATA.firstChild.nextSibling;
		this.PARTS.CONTENT_TOP = this.PARTS.CONTENT_DATA.insertBefore(BX.create('DIV', {
			props: {
				className: 'bx-debug-content-top'
			},
			html: arHtml[0]
		}), this.PARTS.CONTENT_INNER);
		this.PARTS.CONTENT_INNER.style.overflow = 'auto';
	}
	else
	{
		BX.CDebugDialog.superclass.SetContent.apply(this, arguments);
	}
};

BX.CDebugDialog.prototype.__adjustSize = function()
{
	BX.CDebugDialog.superclass.__adjustSize.apply(this, arguments);

	if (this.PARTS.CONTENT_TOP)
	{
		var new_height = this.PARTS.CONTENT_DATA.offsetHeight - this.PARTS.HEAD.offsetHeight - this.PARTS.CONTENT_TOP.offsetHeight - 38;

		if (new_height > 0)
		{
			this.PARTS.CONTENT_INNER.style.height = new_height + 'px';
		}
	}
};


/* class for dialog window with editors */

BX.CEditorDialog = function(arParams)
{
	BX.CEditorDialog.superclass.constructor.apply(this, arguments);

	BX.removeClass(this.PARTS.CONTENT, 'bx-core-adm-dialog-content-wrap');
	BX.removeClass(this.PARTS.CONTENT_DATA, 'bx-core-adm-dialog-content');

	BX.removeClass(this.PARTS.CONTENT_DATA.lastChild, 'bx-core-adm-dialog-content-wrap-inner');
	BX.removeClass(this.PARTS.BUTTONS_CONTAINER, 'bx-core-adm-dialog-buttons');

	BX.addClass(this.PARTS.CONTENT, 'bx-core-editor-dialog-content-wrap');
	BX.addClass(this.PARTS.CONTENT_DATA, 'bx-core-editor-dialog-content');
	BX.addClass(this.PARTS.BUTTONS_CONTAINER, 'bx-core-editor-dialog-buttons');
};
BX.extend(BX.CEditorDialog, BX.CDialog);

BX.CEditorDialog.prototype.SetContent  = function()
{
	BX.CEditorDialog.superclass.SetContent.apply(this, arguments);

	BX.removeClass(this.PARTS.CONTENT_DATA.lastChild, 'bx-core-adm-dialog-content-wrap-inner');
};

/* class for wizards in admin section */
BX.CWizardDialog = function(arParams)
{
	BX.CWizardDialog.superclass.constructor.apply(this, arguments);

	BX.removeClass(this.PARTS.CONTENT, 'bx-core-adm-dialog-content-wrap');
	BX.removeClass(this.PARTS.CONTENT_DATA, 'bx-core-adm-dialog-content');
	BX.removeClass(this.PARTS.CONTENT_DATA.lastChild, 'bx-core-adm-dialog-content-wrap-inner');
	BX.removeClass(this.PARTS.BUTTONS_CONTAINER, 'bx-core-adm-dialog-buttons');

	BX.addClass(this.PARTS.CONTENT, 'bx-core-wizard-dialog-content-wrap');
};

BX.extend(BX.CWizardDialog, BX.CDialog);

/* class for auth dialog */
BX.CAuthDialog = function(arParams)
{
	arParams.resizable = false;
	arParams.width = 350;
	arParams.height = 200;

	arParams.buttons = [this.btnSave];

	BX.CAuthDialog.superclass.constructor.apply(this, arguments);
	this._sender = 'core_window_cauthdialog';

	BX.addClass(this.DIV, 'bx-core-auth-dialog');

	BX.AUTHAGENT = this;
};
BX.extend(BX.CAuthDialog, BX.CDialog);

BX.CAuthDialog.prototype.btnSave = BX.CAuthDialog.btnSave = {
	title: BX.message('JS_CORE_WINDOW_AUTH'),
	id: 'savebtn',
	name: 'savebtn',
	className: 'adm-btn-save',
	action: function () {
		this.disableUntilError();
		this.parentWindow.Submit('', this.parentWindow.PARAMS.content_url);
	}
};

BX.CAuthDialog.prototype.SetError = function(error)
{
	BX.closeWait();

	if (!!error)
		this.ShowError(error.MESSAGE || error);
};

BX.CAuthDialog.prototype.setAuthResult = function(result)
{
	BX.closeWait();

	if (result === false)
	{
		this.Close();
		if (this.PARAMS.callback)
			this.PARAMS.callback();
	}
	else
	{
		this.SetError(result);
	}
};

/* MENU CLASSES */

BX.CWindowFloat = function(node)
{
	BX.CWindowFloat.superclass.constructor.apply(this, [node, 'float']);

	this.SETTINGS.resizable = false;
};
BX.extend(BX.CWindowFloat, BX.CWindow);

BX.CWindowFloat.prototype.adjustPos = function()
{
	if (this.PARAMS.parent)
		this.adjustToNode();
	else if (this.PARAMS.x && this.PARAMS.y)
		this.adjustToPos([this.PARAMS.x, this.PARAMS.y]);
};

BX.CWindowFloat.prototype.adjustToPos = function(pos)
{
	this.DIV.style.left = parseInt(pos[0]) + 'px';
	this.DIV.style.top = parseInt(pos[1]) + 'px';
};

BX.CWindowFloat.prototype.adjustToNodeGetPos = function()
{
	return BX.pos(this.PARAMS.parent);
};

BX.CWindowFloat.prototype.adjustToNode = function(el)
{
	el = el || this.PARAMS.parent;

	this.PARAMS.parent = BX(el);

	if (this.PARAMS.parent)
	{
		var pos = this.adjustToNodeGetPos();

		this.DIV.style.top = pos.top + 'px';//(pos.top - 26) + 'px';
		this.DIV.style.left = pos.left + 'px';

		this.PARAMS.parent.OPENER = this;
	}
};

BX.CWindowFloat.prototype.Show = function()
{
	this.adjustToPos([-1000, -1000]);
	BX.CWindowFloat.superclass.Show.apply(this, arguments);
	this.adjustPos();
};

/* menu opener class */
/*
{
	DOMNode DIV,
	BX.CMenu or Array MENU,
	TYPE = 'hover' | 'click',
	TIMEOUT: 1000
	ATTACH_MODE: 'top' | 'right'
	ACTIVE_CLASS: className for opener element when menu is opened
}
*/
BX.COpener = function(arParams)
{
	this.PARAMS = arParams || {};

	this.MENU = arParams.MENU || [];

	this.DIV = arParams.DIV;
	this.ATTACH = arParams.ATTACH || arParams.DIV;
	this.ATTACH_MODE = arParams.ATTACH_MODE || 'bottom';

	this.ACTIVE_CLASS = arParams.ACTIVE_CLASS || '';
	this.PUBLIC_FRAME = arParams.PUBLIC_FRAME || 0;
	this.LEVEL = arParams.LEVEL || 0;

	this.CLOSE_ON_CLICK = typeof arParams.CLOSE_ON_CLICK != 'undefined' ? !!arParams.CLOSE_ON_CLICK : true;
	this.ADJUST_ON_CLICK = typeof arParams.ADJUST_ON_CLICK != 'undefined' ? !!arParams.ADJUST_ON_CLICK : true;

	this.TYPE = this.PARAMS.TYPE == 'hover' ? 'hover' : 'click';

	this._openTimeout = null;

	if (this.PARAMS.TYPE == 'hover' && arParams.TIMEOUT !== 0)
		this.TIMEOUT = arParams.TIMEOUT || 1000;
	else
		this.TIMEOUT = 0;

	this.bMenuInit = false;

	if (!!this.PARAMS.MENU_URL)
	{
		this.bMenuLoaded = false;
		this.bMenuLoading = false;

		this.MENU = [{
			TEXT: BX.message('JS_CORE_LOADING'),
			CLOSE_ON_CLICK: false
		}];

		if (this.PARAMS.MENU_PRELOAD)
		{
			BX.defer(this.Load, this)();
		}
	}

	BX.ready(BX.defer(this.Init, this));
};

BX.COpener.prototype.Init = function()
{
	this.DIV = BX(this.DIV);

	switch (this.TYPE)
	{
		case 'hover':
			BX.bind(this.DIV, 'mouseover', BX.proxy(this.Open, this));
			BX.bind(this.DIV, 'click', BX.proxy(this.Toggle, this));
		break;

		case 'click':
			BX.bind(this.DIV, 'click', BX.proxy(this.Toggle, this));
		break;
	}

	//BX.bind(window, 'scroll', BX.delegate(this.__close_immediately, this));

	//this.bMenuInit = false;
};

BX.COpener.prototype.Load = function()
{
	if (this.PARAMS.MENU_URL && !this.bMenuLoaded)
	{
		if (!this.bMenuLoading)
		{
			var url = this.PARAMS.MENU_URL;
			if (url.indexOf('sessid=') <= 0)
				url += (url.indexOf('?') > 0 ? '&' : '?') + 'sessid=' + BX.bitrix_sessid();

			this.bMenuLoading = true;
			BX.ajax.loadJSON(url, BX.proxy(this.SetMenu, this), BX.proxy(this.LoadFailed, this));
		}
	}
};

BX.COpener.prototype.SetMenu = function(menu)
{
	this.bMenuLoaded = true;
	this.bMenuLoading = false;
	if (this.bMenuInit)
	{
		this.MENU.setItems(menu);
	}
	else
	{
		this.MENU = menu;
	}
};

BX.COpener.prototype.LoadFailed = function(type, error)
{
	this.bMenuLoading = false;
	this.SetMenu([{
		TEXT: BX.message('JS_CORE_NO_DATA'),
		CLOSE_ON_CLICK: true
	}]);
	BX.debug(arguments);
};

BX.COpener.prototype.checkAdminMenu = function()
{
	if (document.documentElement.id == 'bx-admin-prefix')
		return true;

	return !!BX.findParent(this.DIV, {property: {id: 'bx-admin-prefix'}});
};

BX.COpener.prototype.Toggle = function(e)
{
	this.__clear_timeout();

	if (!this.bMenuInit || !this.MENU.visible())
	{
		var t = this.TIMEOUT;
		this.TIMEOUT = 0;
		this.Open(e);
		this.TIMEOUT = t;
	}
	else
	{
		this.MENU.Close();
	}

	return !!(e||window.event) && BX.PreventDefault(e);
};

BX.COpener.prototype.GetMenu = function()
{
	if (!this.bMenuInit)
	{
		if (BX.type.isArray(this.MENU))
		{
			this.MENU = new BX.CMenu({
				ITEMS: this.MENU,
				ATTACH_MODE: this.ATTACH_MODE,
				SET_ID: this.checkAdminMenu() ? 'bx-admin-prefix' : '',
				CLOSE_ON_CLICK: !!this.CLOSE_ON_CLICK,
				ADJUST_ON_CLICK: !!this.ADJUST_ON_CLICK,
				PUBLIC_FRAME: !!this.PUBLIC_FRAME,
				LEVEL: this.LEVEL,
				parent: BX(this.DIV),
				parent_attach: BX(this.ATTACH)
			});

			if (this.LEVEL > 0)
			{
				BX.bind(this.MENU.DIV, 'mouseover', BX.proxy(this._on_menu_hover, this));
				BX.bind(this.MENU.DIV, 'mouseout', BX.proxy(this._on_menu_hout, this));
			}
		}

		BX.addCustomEvent(this.MENU, 'onMenuOpen', BX.proxy(this.handler_onopen, this));
		BX.addCustomEvent(this.MENU, 'onMenuClose', BX.proxy(this.handler_onclose, this));

		BX.addCustomEvent('onMenuItemHover', BX.proxy(this.handler_onover, this));

		this.bMenuInit = true;
	}

	return this.MENU;
};

BX.COpener.prototype.Open = function()
{
	this.GetMenu();

	this.bOpen = true;

	this.__clear_timeout();

	if (this.TIMEOUT > 0)
	{
		BX.bind(this.DIV, 'mouseout', BX.proxy(this.__clear_timeout, this));
		this._openTimeout = setTimeout(BX.proxy(this.__open, this), this.TIMEOUT);
	}
	else
	{
		this.__open();
	}

	if (!!this.PARAMS.MENU_URL && !this.bMenuLoaded)
	{
		this._loadTimeout = setTimeout(BX.proxy(this.Load, this), parseInt(this.TIMEOUT/2));
	}

	return true;
};

BX.COpener.prototype.__clear_timeout = function()
{
	if (!!this._openTimeout)
		clearTimeout(this._openTimeout);
	if (!!this._loadTimeout)
		clearTimeout(this._loadTimeout);

	BX.unbind(this.DIV, 'mouseout', BX.proxy(this.__clear_timeout, this));
};

BX.COpener.prototype._on_menu_hover = function()
{
	this.bMenuHover = true;

	this.__clear_timeout();

	if (this.ACTIVE_CLASS)
		BX.addClass(this.DIV, this.ACTIVE_CLASS);

};

BX.COpener.prototype._on_menu_hout = function()
{
	this.bMenuHover = false;
};

BX.COpener.prototype.handler_onover = function(level, opener)
{
	if (this.bMenuHover)
		return;

	if (opener != this && level == this.LEVEL-1 && this.ACTIVE_CLASS)
	{
		BX.removeClass(this.DIV, this.ACTIVE_CLASS);
	}

	if (this.bMenuInit && level <= this.LEVEL-1 && this.MENU.visible())
	{
		if (opener != this)
		{
			this.__clear_timeout();
			this._openTimeout = setTimeout(BX.proxy(this.Close, this), this.TIMEOUT);
		}
	}
};

BX.COpener.prototype.handler_onopen = function()
{
	this.bOpen = true;

	if (this.ACTIVE_CLASS)
		BX.addClass(this.DIV, this.ACTIVE_CLASS);

	BX.defer(function() {
		BX.onCustomEvent(this, 'onOpenerMenuOpen');
	}, this)();
};

BX.COpener.prototype.handler_onclose = function()
{
	this.bOpen = false;
	BX.onCustomEvent(this, 'onOpenerMenuClose');

	if (this.ACTIVE_CLASS)
		BX.removeClass(this.DIV, this.ACTIVE_CLASS);
};

BX.COpener.prototype.Close = function()
{
	if (!this.bMenuInit)
		return;

	if (!!this._openTimeout)
		clearTimeout(this._openTimeout);

	this.bOpen = false;

	this.__close();
};

BX.COpener.prototype.__open = function()
{
	this.__clear_timeout();

	if (this.bMenuInit && this.bOpen && !this.MENU.visible())
		this.MENU.Show();
};

BX.COpener.prototype.__close = function()
{
	if (this.bMenuInit && !this.bOpen && this.MENU.visible())
		this.MENU.Hide();
};

BX.COpener.prototype.__close_immediately = function() {
	this.bOpen = false; this.__close();
};

BX.COpener.prototype.isMenuVisible = function() {
	return null != this.MENU.visible && this.MENU.visible()
};

/* common menu class */

BX.CMenu = function(arParams)
{
	BX.CMenu.superclass.constructor.apply(this);

	this.DIV.style.width = 'auto';//this.DIV.firstChild.offsetWidth + 'px';
	this.DIV.style.height = 'auto';//this.DIV.firstChild.offsetHeight + 'px';

	this.PARAMS = arParams || {};
	this.PARTS = {};

	this.PARAMS.ATTACH_MODE = this.PARAMS.ATTACH_MODE || 'bottom';
	this.PARAMS.CLOSE_ON_CLICK = typeof this.PARAMS.CLOSE_ON_CLICK == 'undefined' ? true : this.PARAMS.CLOSE_ON_CLICK;
	this.PARAMS.ADJUST_ON_CLICK = typeof this.PARAMS.ADJUST_ON_CLICK == 'undefined' ? true : this.PARAMS.ADJUST_ON_CLICK;
	this.PARAMS.PUBLIC_FRAME = typeof this.PARAMS.PUBLIC_FRAME == 'undefined' ? false : this.PARAMS.PUBLIC_FRAME;
	this.PARAMS.LEVEL = this.PARAMS.LEVEL || 0;

	this.DIV.className = 'bx-core-popup-menu bx-core-popup-menu-' + this.PARAMS.ATTACH_MODE + ' bx-core-popup-menu-level' + this.PARAMS.LEVEL + (typeof this.PARAMS.ADDITIONAL_CLASS != 'undefined' ? ' ' + this.PARAMS.ADDITIONAL_CLASS : '');
	if (!!this.PARAMS.SET_ID)
		this.DIV.id = this.PARAMS.SET_ID;

	if (this.PARAMS.LEVEL == 0)
	{
		this.ARROW = this.DIV.appendChild(BX.create('SPAN', {props: {className: 'bx-core-popup-menu-angle'}, style: {left:'15px'}}));
	}

	if (!!this.PARAMS.CLASS_NAME)
		this.DIV.className += ' ' + this.PARAMS.CLASS_NAME;

	BX.bind(this.DIV, 'click', BX.eventCancelBubble);

	this.ITEMS = [];

	this.setItems(this.PARAMS.ITEMS);

	BX.addCustomEvent('onMenuOpen', BX.proxy(this._onMenuOpen, this));
	BX.addCustomEvent('onMenuItemSelected', BX.proxy(this.Hide, this));
};
BX.extend(BX.CMenu, BX.CWindowFloat);

BX.CMenu.broadcastCloseEvent = function()
{
	BX.onCustomEvent("onMenuItemSelected");
};

BX.CMenu._toggleChecked = function()
{
	BX.toggleClass(this, 'bx-core-popup-menu-item-checked');
};

BX.CMenu._itemDblClick = function()
{
	window.location.href = this.href;
};

BX.CMenu.prototype.toggleArrow = function(v)
{
	if (!!this.ARROW)
	{
		if (typeof v == 'undefined')
		{
			v = this.ARROW.style.visibility == 'hidden';
		}

		this.ARROW.style.visibility = !!v ? 'visible' : 'hidden';
	}
};

BX.CMenu.prototype.visible = function()
{
	return this.DIV.style.display !== 'none';
};

BX.CMenu.prototype._onMenuOpen = function(menu, menu_level)
{
	if (this.visible())
	{
		if (menu_level == this.PARAMS.LEVEL && menu != this)
		{
			this.Hide();
		}
	}
};

BX.CMenu.prototype.onUnRegister = function()
{
	if (!this.visible())
		return;

	this.Hide();
};

BX.CMenu.prototype.setItems = function(items)
{
	this.PARAMS.ITEMS = items;

	BX.cleanNode(this.DIV);

	if (!!this.ARROW)
		this.DIV.appendChild(this.ARROW);

	if (this.PARAMS.ITEMS)
	{
		this.PARAMS.ITEMS = BX.util.array_values(this.PARAMS.ITEMS);

		var bIcons = false;
		var cnt = 0;
		for (var i = 0, len = this.PARAMS.ITEMS.length; i < len; i++)
		{
			if ((i == 0 || i == len-1) && this.PARAMS.ITEMS[i].SEPARATOR)
				continue;

			cnt++;

			if (!bIcons)
				bIcons = !!this.PARAMS.ITEMS[i].GLOBAL_ICON;

			this.addItem(this.PARAMS.ITEMS[i], i);
		}

		// Occam turning in his grave
		if (cnt === 1)
			BX.addClass(this.DIV, 'bx-core-popup-menu-single-item');
		else
			BX.removeClass(this.DIV, 'bx-core-popup-menu-single-item');

		if (!bIcons)
			BX.addClass(this.DIV, 'bx-core-popup-menu-no-icons');
		else
			BX.removeClass(this.DIV, 'bx-core-popup-menu-no-icons');

	}
};

BX.CMenu.prototype.addItem = function(item)
{
	this.ITEMS.push(item);

	if (item.SEPARATOR)
	{
		item.NODE = BX.create(
			'DIV', {props: {className: 'bx-core-popup-menu-separator'}}
		);
	}
	else
	{
		var bHasMenu = (!!item.MENU
			&& (
				(BX.type.isArray(item.MENU) && item.MENU.length > 0)
				|| item.MENU instanceof BX.CMenu
			) || !!item.MENU_URL
		);

		if (item.DISABLED)
		{
			item.CLOSE_ON_CLICK = false;
			item.LINK = null;
			item.ONCLICK = null;
			item.ACTION = null;
		}

		var attrs = {};
		if (!!item.LINK || BX.browser.IsIE() && !BX.browser.IsDoctype())
		{
			attrs.href = item.LINK || 'javascript:void(0)';
		}
		if (this.PARAMS.PUBLIC_FRAME)
		{
			attrs.target = '_top';
		}

		item.NODE = BX.create(!!item.LINK || BX.browser.IsIE() && !BX.browser.IsDoctype() ? 'A' : 'SPAN', {
			props: {
				className: 'bx-core-popup-menu-item'
					+ (bHasMenu ? ' bx-core-popup-menu-item-opener' : '')
					+ (!!item.DEFAULT ? ' bx-core-popup-menu-item-default' : '')
					+ (!!item.DISABLED ? ' bx-core-popup-menu-item-disabled' : '')
					+ (!!item.CHECKED ? ' bx-core-popup-menu-item-checked' : ''),
					title: !!BX.message['MENU_ENABLE_TOOLTIP'] || !!item.SHOW_TITLE ? item.TITLE || '' : '',
				BXMENULEVEL: this.PARAMS.LEVEL
			},
			attrs: attrs,
			events: {
				mouseover: function()
				{
					BX.onCustomEvent('onMenuItemHover', [this.BXMENULEVEL, this.OPENER])
				}
			},
			html: '<span class="bx-core-popup-menu-item-icon' + (item.GLOBAL_ICON ? ' '+item.GLOBAL_ICON : '') + '"></span><span class="bx-core-popup-menu-item-text">'+(item.HTML||(item.TEXT? BX.util.htmlspecialchars(item.TEXT) : ''))+'</span>'
		});

		if (bHasMenu && !item.DISABLED)
		{
			item.NODE.OPENER = new BX.COpener({
				DIV: item.NODE,
				ACTIVE_CLASS: 'bx-core-popup-menu-item-opened',
				TYPE: 'hover',
				MENU: item.MENU,
				MENU_URL: item.MENU_URL,
				MENU_PRELOAD: !!item.MENU_PRELOAD,
				LEVEL: this.PARAMS.LEVEL + 1,
				ATTACH_MODE:'right',
				TIMEOUT: 500
			});
		}
		else if (this.PARAMS.CLOSE_ON_CLICK && (typeof item.CLOSE_ON_CLICK == 'undefined' || !!item.CLOSE_ON_CLICK))
		{
			BX.bind(item.NODE, 'click', BX.CMenu.broadcastCloseEvent);
		}
		else if (this.PARAMS.ADJUST_ON_CLICK && (typeof item.ADJUST_ON_CLICK == 'undefined' || !!item.ADJUST_ON_CLICK))
		{
			BX.bind(item.NODE, 'click', BX.defer(this.adjustPos, this));
		}

		if (bHasMenu && !!item.LINK)
		{
			BX.bind(item.NODE, 'dblclick', BX.CMenu._itemDblClick);
		}

		if (typeof item.CHECKED != 'undefined')
		{
			BX.bind(item.NODE, 'click', BX.CMenu._toggleChecked);
		}

		item.ONCLICK = item.ACTION || item.ONCLICK;
		if (!!item.ONCLICK)
		{
			if (BX.type.isString(item.ONCLICK))
			{
				item.ONCLICK = new Function("event", item.ONCLICK);
			}

			BX.bind(item.NODE, 'click', item.ONCLICK);
		}
	}

	this.DIV.appendChild(item.NODE);
};

BX.CMenu.prototype._documentClickBind = function()
{
	this._documentClickUnBind();
	BX.bind(document, 'click', BX.proxy(this._documentClick, this));
};

BX.CMenu.prototype._documentClickUnBind = function()
{
	BX.unbind(document, 'click', BX.proxy(this._documentClick, this));
};

BX.CMenu.prototype._documentClick = function(e)
{
	e = e||window.event;
	if(!!e && !(BX.getEventButton(e) & BX.MSLEFT))
		return;

	this.Close();
};

BX.CMenu.prototype.Show = function()
{
	BX.onCustomEvent(this, 'onMenuOpen', [this, this.PARAMS.LEVEL]);
	BX.CMenu.superclass.Show.apply(this, []);

	this.bCloseEventFired = false;

	BX.addCustomEvent(this.PARAMS.parent_attach, 'onChangeNodePosition', BX.proxy(this.adjustToNode, this));

	(BX.defer(this._documentClickBind, this))();
};

BX.CMenu.prototype.Close = // we shouldn't 'Close' window - only hide
BX.CMenu.prototype.Hide = function()
{
	if (!this.visible())
		return;

	BX.removeCustomEvent(this.PARAMS.parent_attach, 'onChangeNodePosition', BX.proxy(this.adjustToNode, this));

	this._documentClickUnBind();

	if (!this.bCloseEventFired)
	{
		BX.onCustomEvent(this, 'onMenuClose', [this, this.PARAMS.LEVEL]);
		this.bCloseEventFired = true;
	}
	BX.CMenu.superclass.Hide.apply(this, arguments);


//	this.DIV.onclick = null;
	//this.PARAMS.parent.onclick = null;
};

BX.CMenu.prototype.__adjustMenuToNode = function()
{
	var pos = BX.pos(this.PARAMS.parent_attach),
		bFixed = !!BX.findParent(this.PARAMS.parent_attach, BX.is_fixed);

	if (bFixed)
		this.DIV.style.position = 'fixed';
	else
		this.DIV.style.position = 'absolute';

	if (!pos.top)
	{
		this.DIV.style.top = '-1000px';
		this.DIV.style.left = '-1000px';
	}

	if (this.bTimeoutSet) return;

	var floatWidth = this.DIV.offsetWidth, floatHeight = this.DIV.offsetHeight;
	if (!floatWidth)
	{
		setTimeout(BX.delegate(function(){
			this.bTimeoutSet = false; this.__adjustMenuToNode();
		}, this), 100);

		this.bTimeoutSet = true;
		return;
	}

	var menu_pos = {},
		wndSize = BX.GetWindowSize();

/*
	if (BX.browser.IsIE() && !BX.browser.IsDoctype())
	{
		pos.top -= 4; pos.bottom -= 4;
		pos.left -= 2; pos.right -= 2;
	}
*/

	switch (this.PARAMS.ATTACH_MODE)
	{
		case 'bottom':
			menu_pos.top = pos.bottom + 9;
			menu_pos.left = pos.left;

			var arrowPos = 0;
			if (!!this.ARROW)
			{
				if (pos.width > floatWidth)
					arrowPos = parseInt(floatWidth/2 - 7);
				else
					arrowPos = parseInt(Math.min(floatWidth, pos.width)/2 - 7);

				if (arrowPos < 7)
				{
					menu_pos.left -= 15;
					arrowPos += 15;
				}
			}

			if (menu_pos.left > wndSize.scrollWidth - floatWidth - 10)
			{
				var orig_menu_pos = menu_pos.left;
				menu_pos.left = wndSize.scrollWidth - floatWidth - 10;

				if (!!this.ARROW)
					arrowPos += orig_menu_pos - menu_pos.left;
			}

			if (bFixed)
			{
				menu_pos.left -= wndSize.scrollLeft;
			}

			if (!!this.ARROW)
				this.ARROW.style.left = arrowPos + 'px';
		break;
		case 'right':
			menu_pos.top = pos.top-1;
			menu_pos.left = pos.right;

			if (menu_pos.left > wndSize.scrollWidth - floatWidth - 10)
			{
				menu_pos.left = pos.left - floatWidth - 1;
			}
		break;
	}

	if (bFixed)
	{
		menu_pos.top -= wndSize.scrollTop;
	}

	if (!!this.ARROW)
		this.ARROW.className = 'bx-core-popup-menu-angle';

	if((menu_pos.top + floatHeight > wndSize.scrollTop + wndSize.innerHeight)
		|| (menu_pos.top + floatHeight > wndSize.scrollHeight))
	{
		var new_top = this.PARAMS.ATTACH_MODE == 'bottom'
			? pos.top - floatHeight - 9
			: pos.bottom - floatHeight + 1;

		if((new_top > wndSize.scrollTop)
			|| (menu_pos.top + floatHeight > wndSize.scrollHeight))
		{
			if ((menu_pos.top + floatHeight > wndSize.scrollHeight))
			{
				menu_pos.top = Math.max(0, wndSize.scrollHeight-floatHeight);
				this.toggleArrow(false);
			}
			else
			{
				menu_pos.top = new_top;

				if (!!this.ARROW)
					this.ARROW.className = 'bx-core-popup-menu-angle-bottom';
			}
		}
	}

	if (menu_pos.top + menu_pos.left == 0)
	{
		this.Hide();
	}
	else
	{
		this.DIV.style.top = menu_pos.top + 'px';
		this.DIV.style.left = menu_pos.left + 'px';
	}
};

BX.CMenu.prototype.adjustToNode = function(el)
{
	this.PARAMS.parent_attach = BX(el) || this.PARAMS.parent_attach || this.PARAMS.parent;
	this.__adjustMenuToNode();
};


/* components toolbar class */

BX.CMenuOpener = function(arParams)
{
	BX.CMenuOpener.superclass.constructor.apply(this);

	this.PARAMS = arParams || {};
	this.setParent(this.PARAMS.parent);
	this.PARTS = {};

	this.SETTINGS.drag_restrict = true;

	this.defaultAction = null;

	this.timeout = 500;

	this.DIV.className = 'bx-component-opener';
	this.DIV.ondblclick = BX.PreventDefault;

	if (this.PARAMS.component_id)
	{
		this.PARAMS.transform = !!this.PARAMS.transform;
	}

	this.OPENERS = [];

	this.DIV.appendChild(BX.create('SPAN', {
		props: {className: 'bx-context-toolbar' + (this.PARAMS.transform ? ' bx-context-toolbar-vertical-mode' : '')}
	}));

	//set internal structure and register draggable element
	this.PARTS.INNER = this.DIV.firstChild.appendChild(BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-inner'},
		html: '<span class="bx-context-toolbar-drag-icon"></span><span class="bx-context-toolbar-vertical-line"></span><br>'
	}));

	this.EXTRA_BUTTONS = {};

	var btnCount = 0;
	for (var i = 0, len = this.PARAMS.menu.length; i < len; i++)
	{
		var item = this.addItem(this.PARAMS.menu[i]);
		if (null != item)
		{
			btnCount++;
			this.PARTS.INNER.appendChild(item);
			this.PARTS.INNER.appendChild(BX.create('BR'));
		}
	}
	var bHasButtons = btnCount > 0;

	//menu items will be attached here

	this.PARTS.ICONS = this.PARTS.INNER.appendChild(BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-icons'}
	}));

	if (this.PARAMS.component_id)
	{
		this.PARAMS.pin = !!this.PARAMS.pin;

		if (bHasButtons)
			this.PARTS.ICONS.appendChild(BX.create('SPAN', {props: {className: 'bx-context-toolbar-separator'}}));

		this.PARTS.ICON_PIN = this.PARTS.ICONS.appendChild(BX.create('A', {
			attrs: {
				href: 'javascript:void(0)'
			},
			props: {
				className: this.PARAMS.pin
							? 'bx-context-toolbar-pin-fixed'
							: 'bx-context-toolbar-pin'
			},
			events: {
				click: BX.delegate(this.__pin_btn_clicked, this)
			}
		}));
	}


	if (this.EXTRA_BUTTONS['components2_props'])
	{
		var btn = this.EXTRA_BUTTONS['components2_props'] || {URL: 'javascript:void(0)'};
		if (null == this.defaultAction)
		{
			this.defaultAction = btn.ONCLICK;
			this.defaultActionTitle = btn.TITLE || btn.TEXT;
		}

		btn.URL = 'javascript:' + BX.util.urlencode(btn.ONCLICK);

		this.ATTACH = this.PARTS.ICONS.appendChild(BX.create('SPAN', {
			props: {className: 'bx-context-toolbar-button bx-context-toolbar-button-settings' },
			children:
			[
				BX.create('SPAN',
				{
					props:{className: 'bx-context-toolbar-button-inner'},
					children:
					[
						BX.create('A', {
							attrs: {href: btn.URL},
							events: {
								mouseover: BX.proxy(this.__msover_text, this),
								mouseout: BX.proxy(this.__msout_text, this),
								mousedown: BX.proxy(this.__msdown_text, this)
							},
							html: '<span class="bx-context-toolbar-button-icon bx-context-toolbar-settings-icon"></span>'
						}),
						BX.create('A', {
							attrs: {href: 'javascript: void(0)'},
							props: {className: 'bx-context-toolbar-button-arrow'},
							events: {
								mouseover: BX.proxy(this.__msover_arrow, this),
								mouseout: BX.proxy(this.__msout_arrow, this),
								mousedown: BX.proxy(this.__msdown_arrow, this)
							},
							html: '<span class="bx-context-toolbar-button-arrow"></span>'
						})
					]
				})
			]
		}));

		this.OPENER = this.ATTACH.firstChild.lastChild;

		var opener = this.attachMenu(this.EXTRA_BUTTONS['components2_submenu']['MENU']);

		BX.addCustomEvent(opener, 'onOpenerMenuOpen', BX.proxy(this.__menu_open, this));
		BX.addCustomEvent(opener, 'onOpenerMenuClose', BX.proxy(this.__menu_close, this));
	}

	if (btnCount > 1)
	{
		this.PARTS.ICONS.appendChild(BX.create('span', { props: {className: 'bx-context-toolbar-separator bx-context-toolbar-separator-switcher'}}));

		this.ICON_TRANSFORM = this.PARTS.ICONS.appendChild(BX.create('A', {
			attrs: {href: 'javascript: void(0)'},
			props: {className: 'bx-context-toolbar-switcher'},
			events: {
				click: BX.delegate(this.__trf_btn_clicked, this)
			}
		}));
	}

	if (this.PARAMS.HINT)
	{
		this.DIV.BXHINT = this.HINT = new BX.CHint({
			parent: this.DIV,
			hint:this.PARAMS.HINT.TEXT || '',
			title: this.PARAMS.HINT.TITLE || '',
			hide_timeout: this.timeout/2,
			preventHide: false
		});
	}

	BX.addCustomEvent(this, 'onWindowDragFinished', BX.delegate(this.__onMoveFinished, this));
	BX.addCustomEvent('onDynamicModeChange', BX.delegate(this.__onDynamicModeChange, this));
	BX.addCustomEvent('onTopPanelCollapse', BX.delegate(this.__onPanelCollapse, this));

	BX.addCustomEvent('onMenuOpenerMoved', BX.delegate(this.checkPosition, this));
	BX.addCustomEvent('onMenuOpenerUnhide', BX.delegate(this.checkPosition, this));

	BX.Event.EventEmitter.incrementMaxListeners('onDynamicModeChange');
	BX.Event.EventEmitter.incrementMaxListeners('onTopPanelCollapse');
	BX.Event.EventEmitter.incrementMaxListeners('onMenuOpenerMoved');
	BX.Event.EventEmitter.incrementMaxListeners('onMenuOpenerUnhide');

	if (this.OPENERS)
	{
		for (i=0,len=this.OPENERS.length; i<len; i++)
		{
			BX.addCustomEvent(this.OPENERS[i], 'onOpenerMenuOpen', BX.proxy(this.__hide_hint, this));
		}
	}
};
BX.extend(BX.CMenuOpener, BX.CWindowFloat);

BX.CMenuOpener.prototype.setParent = function(new_parent)
{
	new_parent = BX(new_parent);

	if (!new_parent)
	{
		return;
	}

	if(new_parent.OPENER && new_parent.OPENER != this)
	{
		new_parent.OPENER.Close();
		new_parent.OPENER.clearHoverHoutEvents();
	}

	if(this.PARAMS.parent && this.PARAMS.parent != new_parent)
	{
		this.clearHoverHoutEvents();
		this.PARAMS.parent.OPENER = null;
	}

	this.PARAMS.parent = new_parent;
	this.PARAMS.parent.OPENER = this;
};

BX.CMenuOpener.prototype.setHoverHoutEvents = function(hover, hout)
{
	if(!this.__opener_events_set)
	{
		BX.bind(this.Get(), 'mouseover', hover);
		BX.bind(this.Get(), 'mouseout', hout);
		this.__opener_events_set = true;
	}
};

BX.CMenuOpener.prototype.clearHoverHoutEvents = function()
{
	if(this.Get())
	{
		BX.unbindAll(this.Get());
		this.__opener_events_set = false;
	}
};


BX.CMenuOpener.prototype.unclosable = true;

BX.CMenuOpener.prototype.__check_intersection = function(pos_self, pos_other)
{
	return !(pos_other.right <= pos_self.left || pos_other.left >= pos_self.right
			|| pos_other.bottom <= pos_self.top || pos_other.top >= pos_self.bottom);
};


BX.CMenuOpener.prototype.__msover_text = function() {
	this.bx_hover = true;
	if (!this._menu_open)
		BX.addClass(this.ATTACH, 'bx-context-toolbar-button-text-hover');
};

BX.CMenuOpener.prototype.__msout_text = function() {
	this.bx_hover = false;
	if (!this._menu_open)
		BX.removeClass(this.ATTACH, 'bx-context-toolbar-button-text-hover bx-context-toolbar-button-text-active');
};

BX.CMenuOpener.prototype.__msover_arrow = function() {
	this.bx_hover = true;
	if (!this._menu_open)
		BX.addClass(this.ATTACH, 'bx-context-toolbar-button-arrow-hover');
};

BX.CMenuOpener.prototype.__msout_arrow = function() {
	this.bx_hover = false;
	if (!this._menu_open)
		BX.removeClass(this.ATTACH, 'bx-context-toolbar-button-arrow-hover bx-context-toolbar-button-arrow-active');
};

BX.CMenuOpener.prototype.__msdown_text = function() {
	this.bx_active = true;
	if (!this._menu_open)
		BX.addClass(this.ATTACH, 'bx-context-toolbar-button-text-active');
};

BX.CMenuOpener.prototype.__msdown_arrow = function() {
	this.bx_active = true;
	if (!this._menu_open)
		BX.addClass(this.ATTACH, 'bx-context-toolbar-button-arrow-active');
};

BX.CMenuOpener.prototype.__menu_close = function() {
	this._menu_open = false;
	this.bx_active = false;
	BX.removeClass(this.ATTACH, 'bx-context-toolbar-button-active bx-context-toolbar-button-text-active bx-context-toolbar-button-arrow-active');
	if (!this.bx_hover)
	{
		BX.removeClass(this.ATTACH, 'bx-context-toolbar-button-hover bx-context-toolbar-button-text-hover bx-context-toolbar-button-arrow-hover');
		this.bx_hover = false;
	}
};

BX.CMenuOpener.prototype.__menu_open = function() {
	this._menu_open = true;
};

BX.CMenuOpener.prototype.checkPosition = function()
{
	if (this.isMenuVisible() || this.DIV.style.display == 'none'
		|| this == BX.proxy_context || BX.proxy_context.zIndex > this.zIndex)
		return;

	this.correctPosition(BX.proxy_context);
};

BX.CMenuOpener.prototype.correctPosition = function(opener)
{
	var pos_self = BX.pos(this.DIV), pos_other = BX.pos(opener.Get());
	if (this.__check_intersection(pos_self, pos_other))
	{
		var new_top = pos_other.top - pos_self.height;
		if (new_top < 0)
			new_top = pos_other.bottom;

		this.DIV.style.top = new_top + 'px';

		BX.addCustomEvent(opener, 'onMenuOpenerHide', BX.proxy(this.restorePosition, this));
		BX.onCustomEvent(this, 'onMenuOpenerMoved');
	}
};

BX.CMenuOpener.prototype.restorePosition = function()
{
	if (!this.MOUSEOVER && !this.isMenuVisible())
	{
		if (this.originalPos)
			this.DIV.style.top = this.originalPos.top + 'px';

		BX.removeCustomEvent(BX.proxy_context, 'onMenuOpenerHide', BX.proxy(this.restorePosition, this));
		if (this.restore_pos_timeout) clearTimeout(this.restore_pos_timeout);
	}
	else
	{
		this.restore_pos_timeout = setTimeout(BX.proxy(this.restorePosition, this), this.timeout);
	}
};


BX.CMenuOpener.prototype.Show = function()
{
	BX.CMenuOpener.superclass.Show.apply(this, arguments);

	this.SetDraggable(this.PARTS.INNER.firstChild);

	this.DIV.style.width = 'auto';
	this.DIV.style.height = 'auto';

	if (!this.PARAMS.pin)
	{
		this.DIV.style.left = '-1000px';
		this.DIV.style.top = '-1000px';

		this.Hide();
	}
	else
	{
		this.bPosAdjusted = true;
		this.bMoved = true;

		if (this.PARAMS.top) this.DIV.style.top = this.PARAMS.top + 'px';
		if (this.PARAMS.left) this.DIV.style.left = this.PARAMS.left + 'px';

		this.DIV.style.display = (!BX.admin.dynamic_mode || BX.admin.dynamic_mode_show_borders) ? 'block' : 'none';

		if (this.DIV.style.display == 'block')
		{
			setTimeout(BX.delegate(function() {BX.onCustomEvent(this, 'onMenuOpenerUnhide')}, this), 50);
		}
	}
};

BX.CMenuOpener.prototype.executeDefaultAction = function()
{
	if (this.defaultAction)
	{
		if (BX.type.isFunction(this.defaultAction))
			this.defaultAction();
		else if(BX.type.isString(this.defaultAction))
			BX.evalGlobal(this.defaultAction);
	}
};

BX.CMenuOpener.prototype.__onDynamicModeChange = function(val)
{
	this.DIV.style.display = val ? 'block' : 'none';
};

BX.CMenuOpener.prototype.__onPanelCollapse = function(bCollapsed, dy)
{
	this.DIV.style.top = (parseInt(this.DIV.style.top) + dy) + 'px';
	if (this.PARAMS.pin)
	{
		this.__savePosition();
	}
};

BX.CMenuOpener.prototype.__onMoveFinished = function()
{
	BX.onCustomEvent(this, 'onMenuOpenerMoved');

	this.bMoved = true;

	if (this.PARAMS.pin)
		this.__savePosition();
};

BX.CMenuOpener.prototype.__savePosition = function()
{
	var arOpts = {};

	arOpts.pin = this.PARAMS.pin;
	if (!this.PARAMS.pin)
	{
		arOpts.top = false; arOpts.left = false; arOpts.transform = false;
	}
	else
	{
		arOpts.transform = this.PARAMS.transform;
		if (this.bMoved)
		{
			arOpts.left = parseInt(this.DIV.style.left);
			arOpts.top = parseInt(this.DIV.style.top);
		}
	}

	BX.WindowManager.saveWindowOptions(this.PARAMS.component_id, arOpts);
};

BX.CMenuOpener.prototype.__pin_btn_clicked = function() {this.Pin()};
BX.CMenuOpener.prototype.Pin = function(val)
{
	if (null == val)
		this.PARAMS.pin = !this.PARAMS.pin;
	else
		this.PARAMS.pin = !!val;

	this.PARTS.ICON_PIN.className = (this.PARAMS.pin ? 'bx-context-toolbar-pin-fixed' : 'bx-context-toolbar-pin');

	this.__savePosition();
};

BX.CMenuOpener.prototype.__trf_btn_clicked = function() {this.Transform()};
BX.CMenuOpener.prototype.Transform = function(val)
{
	var pos = {};

	if (null == val)
		this.PARAMS.transform = !this.PARAMS.transform;
	else
		this.PARAMS.transform = !!val;

	if (this.bMoved)
	{
		pos = BX.pos(this.DIV);
	}

	if (this.PARAMS.transform)
		BX.addClass(this.DIV.firstChild, 'bx-context-toolbar-vertical-mode');
	else
		BX.removeClass(this.DIV.firstChild, 'bx-context-toolbar-vertical-mode');

	if (!this.bMoved)
	{
		this.adjustPos();
	}
	else
	{
		this.DIV.style.left = (pos.right - this.DIV.offsetWidth - (BX.browser.IsIE() && !BX.browser.IsDoctype() ? 2 : 0)) + 'px';
	}

	this.__savePosition();
};

BX.CMenuOpener.prototype.adjustToNodeGetPos = function()
{
	var pos = BX.pos(this.PARAMS.parent/*, true*/);

	var scrollSize = BX.GetWindowScrollSize();
	var floatWidth = this.DIV.offsetWidth;

	pos.left -= BX.admin.__border_dx;
	pos.top -= BX.admin.__border_dx;

	if (true || !this.PARAMS.transform)
	{
		pos.top -= 45;
	}

	if (pos.left > scrollSize.scrollWidth - floatWidth)
	{
		pos.left = scrollSize.scrollWidth - floatWidth;
	}

	return pos;
};

BX.CMenuOpener.prototype.addItem = function(item)
{
	if (item.TYPE)
	{
		this.EXTRA_BUTTONS[item.TYPE] = item;
		return null;
	}
	else
	{
		var q = new BX.CMenuOpenerItem(item);
		if (null == this.defaultAction)
		{
			if (q.item.ONCLICK)
			{
				this.defaultAction = item.ONCLICK;
			}
			else if (q.item.MENU)
			{
				this.defaultAction = BX.delegate(function() {this.Open()}, q.item.OPENER);
			}

			this.defaultActionTitle = item.TITLE || item.TEXT;

			BX.addClass(q.Get(), 'bx-content-toolbar-default');
		}
		if (q.item.OPENER) this.OPENERS[this.OPENERS.length] = q.item.OPENER;
		return q.Get();
	}
};

BX.CMenuOpener.prototype.attachMenu = function(menu)
{
	var opener = new BX.COpener({
		'DIV':  this.OPENER,
		'ATTACH': this.ATTACH,
		'MENU': menu,
		'TYPE': 'click'
	});

	this.OPENERS[this.OPENERS.length] = opener;

	return opener;
};

BX.CMenuOpener.prototype.__hide_hint = function()
{
	if (this.HINT) this.HINT.__hide_immediately();
};

BX.CMenuOpener.prototype.isMenuVisible = function()
{
	for (var i=0,len=this.OPENERS.length; i<len; i++)
	{
		if (this.OPENERS[i].isMenuVisible())
			return true;
	}

	return false;
};

BX.CMenuOpener.prototype.Hide = function()
{
	if (!this.PARAMS.pin)
	{
		this.DIV.style.display = 'none';
		BX.onCustomEvent(this, 'onMenuOpenerHide');
	}
};
BX.CMenuOpener.prototype.UnHide = function()
{
	this.DIV.style.display = 'block';
	if (!this.bPosAdjusted && !this.PARAMS.pin)
	{
		this.adjustPos();
		this.bPosAdjusted = true;
	}

	if (null == this.originalPos && !this.bMoved)
	{
		this.originalPos = BX.pos(this.DIV);
	}

	BX.onCustomEvent(this, 'onMenuOpenerUnhide');
};

BX.CMenuOpenerItem = function(item)
{
	this.item = item;

	if (this.item.ACTION && !this.item.ONCLICK)
	{
		this.item.ONCLICK = this.item.ACTION;
	}

	this.DIV = BX.create('SPAN');
	this.DIV.appendChild(BX.create('SPAN', {props: {className: 'bx-context-toolbar-button-underlay'}}));

	this.WRAPPER = this.DIV.appendChild(BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-button-wrapper'},
		children: [
			BX.create('SPAN', {
				props: {className: 'bx-context-toolbar-button', title: item.TITLE},
				children: [
					BX.create('SPAN', {
						props: {className: 'bx-context-toolbar-button-inner'}
					})
				]
			})
		]
	}));

	var btn_icon = BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-button-icon' + (this.item.ICON || this.item.ICONCLASS ? ' ' + (this.item.ICON || this.item.ICONCLASS) : '')},
		attrs: (
				!(this.item.ICON || this.item.ICONCLASS)
				&&
				(this.item.SRC || this.item.IMAGE)
			)
			? {
				style: 'background: scroll transparent url(' + (this.item.SRC || this.item.IMAGE) + ') no-repeat center center !important;'
			}
			: {}
	}), btn_text = BX.create('SPAN', {
		props: {className: 'bx-context-toolbar-button-text'},
		text: this.item.TEXT
	});

	if (this.item.ACTION && !this.item.ONCLICK)
	{
		this.item.ONCLICK = this.item.ACTION;
	}

	this.bHasMenu = !!this.item.MENU;
	this.bHasAction = !!this.item.ONCLICK;

	if (this.bHasAction)
	{
		this.LINK = this.WRAPPER.firstChild.firstChild.appendChild(BX.create('A', {
			attrs: {
				'href': 'javascript: void(0)'
			},
			events: {
				mouseover: this.bHasMenu ? BX.proxy(this.__msover_text, this) : BX.proxy(this.__msover, this),
				mouseout: this.bHasMenu ? BX.proxy(this.__msout_text, this) : BX.proxy(this.__msout, this),
				mousedown: this.bHasMenu ? BX.proxy(this.__msdown_text, this) : BX.proxy(this.__msdown, this)
			},
			children: [btn_icon, btn_text]
		}));

		if (this.bHasMenu)
		{
			this.LINK_MENU = this.WRAPPER.firstChild.firstChild.appendChild(BX.create('A', {
				props: {className: 'bx-context-toolbar-button-arrow'},
				attrs: {
					'href': 'javascript: void(0)'
				},
				events: {
					mouseover: BX.proxy(this.__msover_arrow, this),
					mouseout: BX.proxy(this.__msout_arrow, this),
					mousedown: BX.proxy(this.__msdown_arrow, this)
				},
				children: [
					BX.create('SPAN', {props: {className: 'bx-context-toolbar-button-arrow'}})
				]
			}));
		}

	}
	else if (this.bHasMenu)
	{
		this.item.ONCLICK = null;

		this.LINK = this.LINK_MENU = this.WRAPPER.firstChild.firstChild.appendChild(BX.create('A', {
			attrs: {
				'href': 'javascript: void(0)'
			},
			events: {
				mouseover: BX.proxy(this.__msover, this),
				mouseout: BX.proxy(this.__msout, this),
				mousedown: BX.proxy(this.__msdown, this)
			},
			children: [
				btn_icon,
				btn_text
			]
		}));

		this.LINK.appendChild(BX.create('SPAN', {props: {className: 'bx-context-toolbar-single-button-arrow'}}));

	}

	if (this.bHasMenu)
	{
		this.item.SUBMENU = new BX.CMenu({
			ATTACH_MODE:'bottom',
			ITEMS:this.item['MENU'],
			//PARENT_MENU:this.parentMenu,
			parent: this.LINK_MENU,
			parent_attach: this.WRAPPER.firstChild
		});

		this.item.OPENER = new BX.COpener({
			DIV: this.LINK_MENU,
			TYPE: 'click',
			MENU: this.item.SUBMENU
		});

		BX.addCustomEvent(this.item.OPENER, 'onOpenerMenuOpen', BX.proxy(this.__menu_open, this));
		BX.addCustomEvent(this.item.OPENER, 'onOpenerMenuClose', BX.proxy(this.__menu_close, this));
	}

	if (this.bHasAction)
	{
		BX.bind(this.LINK, 'click', BX.delegate(this.__click, this));
	}
};

BX.CMenuOpenerItem.prototype.Get = function() {return this.DIV;};
BX.CMenuOpenerItem.prototype.__msover = function() {
	this.bx_hover = true;
	if (!this._menu_open)
		BX.addClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-hover');
};
BX.CMenuOpenerItem.prototype.__msout = function() {
	this.bx_hover = false;
	if (!this._menu_open)
		BX.removeClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-hover bx-context-toolbar-button-active');
};
BX.CMenuOpenerItem.prototype.__msover_text = function() {
	this.bx_hover = true;
	if (!this._menu_open)
		BX.addClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-text-hover');
};
BX.CMenuOpenerItem.prototype.__msout_text = function() {
	this.bx_hover = false;
	if (!this._menu_open)
		BX.removeClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-text-hover bx-context-toolbar-button-text-active');
};
BX.CMenuOpenerItem.prototype.__msover_arrow = function() {
	this.bx_hover = true;
	if (!this._menu_open)
		BX.addClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-arrow-hover');
};
BX.CMenuOpenerItem.prototype.__msout_arrow = function() {
	this.bx_hover = false;
	if (!this._menu_open)
		BX.removeClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-arrow-hover bx-context-toolbar-button-arrow-active');
};
BX.CMenuOpenerItem.prototype.__msdown = function() {
	this.bx_active = true;
	if (!this._menu_open)
		BX.addClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-active');
};
BX.CMenuOpenerItem.prototype.__msdown_text = function() {
	this.bx_active = true;
	if (!this._menu_open)
		BX.addClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-text-active');
};
BX.CMenuOpenerItem.prototype.__msdown_arrow = function() {
	this.bx_active = true;
	if (!this._menu_open)
		BX.addClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-arrow-active');
};
BX.CMenuOpenerItem.prototype.__menu_close = function() {

	this._menu_open = false;
	this.bx_active = false;
	BX.removeClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-active bx-context-toolbar-button-text-active bx-context-toolbar-button-arrow-active');
	if (!this.bx_hover)
	{
		BX.removeClass(this.LINK.parentNode.parentNode, 'bx-context-toolbar-button-hover bx-context-toolbar-button-text-hover bx-context-toolbar-button-arrow-hover');
		this.bx_hover = false;
	}
};
BX.CMenuOpenerItem.prototype.__menu_open = function() {
	this._menu_open = true;
};

BX.CMenuOpenerItem.prototype.__click = function() {BX.evalGlobal(this.item.ONCLICK)};

/* global page opener class */
BX.CPageOpener = function(arParams)
{
	//if (null == arParams.pin) arParams.pin = true;
	BX.CPageOpener.superclass.constructor.apply(this, arguments);

	this.timeout = 505;

	window.PAGE_EDIT_CONTROL = this;
};
BX.extend(BX.CPageOpener, BX.CMenuOpener);

BX.CPageOpener.prototype.checkPosition = function()
{
	if (/*this.isMenuVisible() || this.DIV.style.display == 'none' || */this == BX.proxy_context)
		return;

	this.correctPosition(BX.proxy_context);
};

BX.CPageOpener.prototype.correctPosition = function(opener)
{
	if (this.bPosCorrected) return;
	var pos_self;
	if (this.DIV.style.display == 'none')
	{
		pos_self = this.adjustToNodeGetPos();
		pos_self.bottom = pos_self.top + 30;
		pos_self.right = pos_self.left + 300;
	}
	else
	{
		pos_self = BX.pos(this.DIV);
	}

	var pos_other = BX.pos(opener.Get());
	if (this.__check_intersection(pos_self, pos_other))
	{
		this.DIV.style.display = 'none';

		var handler = BX.proxy(this.restorePosition, this);

		BX.removeCustomEvent(opener, 'onMenuOpenerHide', handler);
		BX.addCustomEvent(opener, 'onMenuOpenerHide', handler);

		this.bPosCorrected = true;
	}
};

BX.CPageOpener.prototype.restorePosition = function()
{
	if (BX.proxy_context && BX.proxy_context.Get().style.display == 'none')
	{
		this.bPosCorrected = false;

		if (this.PARAMS.parent.bx_over || this.PARAMS.pin)
			this.UnHide();

		BX.removeCustomEvent('onMenuOpenerHide', BX.proxy(this.restorePosition, this));
	}
};

BX.CPageOpener.prototype.UnHide = function()
{
	if (!this.bPosCorrected)
		BX.CPageOpener.superclass.UnHide.apply(this, arguments);
};

BX.CPageOpener.prototype.Remove = function()
{
	BX.admin.removeComponentBorder(this.PARAMS.parent);
	BX.userOptions.save('global', 'settings', 'page_edit_control_enable', 'N');
	this.DIV.style.display = 'none';
};

/******* HINT ***************/
BX.CHintSimple = function()
{
	BX.CHintSimple.superclass.constructor.apply(this, arguments);
};
BX.extend(BX.CHintSimple, BX.CHint);

BX.CHintSimple.prototype.Init = function()
{
	this.DIV = document.body.appendChild(BX.create('DIV', {props: {className: 'bx-tooltip-simple'}, style: {display: 'none'}, children: [(this.CONTENT = BX.create('DIV'))]}));

	BX.ZIndexManager.register(this.DIV);

	if (this.HINT_TITLE)
		this.CONTENT.appendChild(BX.create('B', {text: this.HINT_TITLE}));

	if (this.HINT)
		this.CONTENT_TEXT = this.CONTENT.appendChild(BX.create('DIV')).appendChild(BX.create('SPAN', {html: this.HINT}));

	if (this.PARAMS.preventHide)
	{
		BX.bind(this.DIV, 'mouseout', BX.proxy(this.Hide, this));
		BX.bind(this.DIV, 'mouseover', BX.proxy(this.Show, this));
	}

	this.bInited = true;
};

/*************************** admin informer **********************************/
BX.adminInformer = {

	itemsShow: 3,

	Init: function (itemsShow)
	{
		if(itemsShow)
			BX.adminInformer.itemsShow = itemsShow;

		var informer = BX("admin-informer");

		if(informer)
		{
			document.body.appendChild(informer);
			BX.ZIndexManager.register(informer);
		}

		BX.addCustomEvent("onTopPanelCollapse", BX.proxy(BX.adminInformer.Close, BX.adminInformer));
	},

	Toggle: function(notifyBlock)
	{
		var informer = BX("admin-informer");

		if(!informer)
			return false;

		var pos = BX.pos(notifyBlock);

		informer.style.top = (parseInt(pos.top)+parseInt(pos.height)+7)+'px';
		informer.style.left = pos.left+'px';

		if(!BX.hasClass(informer, "adm-informer-active"))
			BX.adminInformer.Show(informer);
		else
			BX.adminInformer.Hide(informer);

		return false;
	},

	Close: function()
	{
		BX.adminInformer.Hide(BX("admin-informer"));
	},

	OnInnerClick: function(event)
	{
		var target = event.target || event.srcElement;

		if(target.nodeName.toLowerCase() != 'a' || BX.hasClass(target,"adm-informer-footer"))
		{
			return BX.PreventDefault(event);
		}

		return true;
	},

	ToggleExtra : function()
	{
		var footerLink = BX("adm-informer-footer");

		if (BX.hasClass(footerLink, "adm-informer-footer-collapsed"))
			this.ShowAll();
		else
			this.HideExtra();

		return false;
	},

	ShowAll: function()
	{
		var informer = BX("admin-informer");
		for(var i=0; i<informer.children.length; i++)

			if(BX.hasClass(informer.children[i], "adm-informer-item") && informer.children[i].style.display == "none") {
				informer.children[i].style.display = "block";
			}

		var footerLink = BX("adm-informer-footer");

		if(footerLink.textContent !== undefined)
			footerLink.textContent = BX.message('JSADM_AI_HIDE_EXTRA');
		else
			footerLink.innerText = BX.message('JSADM_AI_HIDE_EXTRA');

		BX.removeClass(footerLink, "adm-informer-footer-collapsed");

		return false;
	},

	HideExtra: function()
	{
		var informer = BX("admin-informer");
		var hided = 0;

		for(var i=BX.adminInformer.itemsShow+1; i<informer.children.length; i++)
		{
			if (BX.hasClass(informer.children[i], "adm-informer-item") && informer.children[i].style.display == "block") {
				informer.children[i].style.display = "none";
				hided++;
			}
		}

		var footerLink = BX("adm-informer-footer");

		var linkText = BX.message('JSADM_AI_ALL_NOTIF')+" ("+(BX.adminInformer.itemsShow+parseInt(hided))+")";

		if(footerLink.textContent !== undefined)
			footerLink.textContent = linkText;
		else
			footerLink.innerText = linkText;

		BX.addClass(footerLink, "adm-informer-footer-collapsed");

		return false;
	},

	Show: function(informer)
	{
		var notifButton = BX("adm-header-notif-block");
		if (notifButton)
			BX.addClass(notifButton, "adm-header-notif-block-active");

		BX.onCustomEvent(informer, 'onBeforeAdminInformerShow');
		setTimeout(
			BX.proxy(function() {
					BX.bind(document, "click", BX.proxy(BX.adminInformer.Close, BX.adminInformer));
				},
				BX.adminInformer
			),0
		);
		BX.addClass(informer, "adm-informer-active");

		BX.ZIndexManager.bringToFront(informer);

		setTimeout(function() {BX.addClass(informer, "adm-informer-animate");},0);
	},

	Hide: function(informer)
	{
		var notifButton = BX("adm-header-notif-block");
		if (notifButton)
			BX.removeClass(notifButton, "adm-header-notif-block-active");

		BX.unbind(document, "click", BX.proxy(BX.adminInformer.Close, BX.adminInformer));

		BX.removeClass(informer, "adm-informer-animate");

		if (this.IsAnimationSupported())
			setTimeout(function() {BX.removeClass(informer, "adm-informer-active");}, 300);
		else
			BX.removeClass(informer, "adm-informer-active");

		BX.onCustomEvent(informer, 'onAdminInformerHide');
		//setTimeout(function() {BX.adminInformer.HideExtra();},500);
	},

	IsAnimationSupported : function()
	{
		var d = document.body || document.documentElement;
		if (typeof(d.style.transition) == "string")
			return true;
		else if (typeof(d.style.MozTransition) == "string")
			return true;
		else if (typeof(d.style.OTransition) == "string")
			return true;
		else if (typeof(d.style.WebkitTransition) == "string")
			return true;
		else if (typeof(d.style.msTransition) == "string")
			return true;

		return false;
	},


	SetItemHtml: function(itemIdx, html)
	{
		var itemHtmlDiv = BX("adm-informer-item-html-"+itemIdx);

		if(!itemHtmlDiv)
			return false;

		itemHtmlDiv.innerHTML = html;

		return true;
	},

	SetItemFooter: function(itemIdx, html)
	{
		var itemFooterDiv = BX("adm-informer-item-footer-"+itemIdx);

		if(!itemFooterDiv)
			return false;

		itemFooterDiv.innerHTML = html;

		if(html)
			itemFooterDiv.style.display = "block";
		else
			itemFooterDiv.style.display = "none";

		return true;
	}

};

})(window);


/* End */
;
; /* Start:"a:4:{s:4:"full";s:52:"/bitrix/js/main/core/core_tooltip.js?174556396715634";s:6:"source";s:36:"/bitrix/js/main/core/core_tooltip.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function(window) {
if (BX.tooltip) return;

var arTooltipIndex = {},
	bDisable = false;

BX.tooltip = function(user_id, anchor_name, loader, rootClassName, bForceUseLoader, params)
{
	if (BX.message('TOOLTIP_ENABLED') != "Y")
	{
		return;
	}

	if (
		BX.browser.IsAndroid()
		|| BX.browser.IsIOS()
	)
	{
		return;
	}

	BX.ready(function() {
		var anchor = BX(anchor_name);
		if (null == anchor)
		{
			return;
		}

		var tooltipId = user_id;
		if(bForceUseLoader && BX.type.isNotEmptyString(loader))
		{
			// prepare tooltip ID from custom loader
			var loaderHash = 0;
			for(var i = 0, len = loader.length; i < len; i++)
			{
				loaderHash = (31 * loaderHash + loader.charCodeAt(i)) << 0;
			}

			tooltipId = loaderHash + user_id;
		}

		if (null == arTooltipIndex[tooltipId])
		{
			arTooltipIndex[tooltipId] = new BX.CTooltip(user_id, anchor, loader, rootClassName, bForceUseLoader, params);
		}
		else
		{
			arTooltipIndex[tooltipId].ANCHOR = anchor;
			arTooltipIndex[tooltipId].rootClassName = rootClassName;
			arTooltipIndex[tooltipId].LOADER = (
				bForceUseLoader
				&& BX.type.isNotEmptyString(loader)
					? loader
					: '/bitrix/tools/tooltip.php'
			);
			arTooltipIndex[tooltipId].params = params;
			arTooltipIndex[tooltipId].Create();
		}
	});
};

BX.tooltip.disable = function(){ bDisable = true; };
BX.tooltip.enable = function(){ bDisable = false; };

BX.tooltip.hide = function(userId) {
	if (BX('user_info_' + userId))
	{
		BX('user_info_' + userId).style.display = 'none';
	}
};

BX.tooltip.openIM = function(userId) {

	const namespace = top.BX.Reflection.namespace('BX.Messenger.Public');
	if (namespace)
	{
		namespace.openChat(userId);
		BX.tooltip.hide(userId);
	}
	else if (BX('MULSonetMessageChatTemplate'))
	{
		window.open(BX('MULSonetMessageChatTemplate').replace('#user_id#', userId).replace('#USER_ID#', userId).replace('#ID#', userId), '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5));
		BX.tooltip.hide(userId);
	}
	return false;
};


BX.tooltip.openCallTo = function(userId) {

	const namespace = top.BX.Reflection.namespace('BX.Messenger.Public');
	if (namespace)
	{
		namespace.startVideoCall(userId);
	}
	return false;
};

BX.tooltip.checkCallTo = function(nodeId) {
};

BX.tooltip.openVideoCall = function(userId) {
	const namespace = top.BX.Reflection.namespace('BX.Messenger.Public');
	if (namespace)
	{
		namespace.startVideoCall(userId);
	}
	else if (BX('MULVideoCallTemplate'))
	{
		window.open(BX('MULVideoCallTemplate').replace('#user_id#', userId).replace('#USER_ID#', userId).replace('#ID#', userId), '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=1000,height=600,top='+Math.floor((screen.height - 600)/2-14)+',left='+Math.floor((screen.width - 1000)/2-5));
		BX.tooltip.hide(userId);
	}
	return false;
};

BX.CTooltip = function(user_id, anchor, loader, rootClassName, bForceUseLoader, params)
{
	this.LOADER = (
		bForceUseLoader
		&& BX.type.isNotEmptyString(loader)
			? loader
			: '/bitrix/tools/tooltip.php'
	);
	this.USER_ID = user_id;
	this.ANCHOR = anchor;
	this.rootClassName = '';
	this.params = (typeof params != 'undefined' ? params : {});

	if (
		rootClassName != 'undefined'
		&& rootClassName != null
		&& rootClassName.length > 0
	)
	{
		this.rootClassName = rootClassName;
	}

	var old = document.getElementById('user_info_' + this.USER_ID);
	if (null != old)
	{
		if (null != old.parentNode)
			old.parentNode.removeChild(old);

		old = null;
	}

	var _this = this;

	this.INFO = null;

	this.width = 393;
	this.height = 302;

	this.RealAnchor = null;
	this.CoordsLeft = 0;
	this.CoordsTop = 0;
	this.AnchorRight = 0;
	this.AnchorBottom = 0;

	this.DIV = null;
	this.ROOT_DIV = null;

	if (BX.browser.IsIE())
	{
		this.IFRAME = null;
	}

	this.v_delta = 0;
	this.classNameAnim = false;
	this.classNameFixed = false;

	this.left = 0;
	this.top = 0;

	this.tracking = false;
	this.active = false;
	this.showed = false;

	this.Create = function()
	{
		_this.ANCHOR.onmouseover = function() {
			if (!bDisable)
			{
				_this.StartTrackMouse(this);
			}
		};

		_this.ANCHOR.onmouseout = function() {
			_this.StopTrackMouse(this);
		}
	};

	this.Create();

	this.TrackMouse = function(e)
	{
		if(!_this.tracking)
			return;

		var current;
		if(e && e.pageX)
			current = {x: e.pageX, y: e.pageY};
		else
			current = {x: e.clientX + document.body.scrollLeft, y: e.clientY + document.body.scrollTop};

		if(current.x < 0)
			current.x = 0;
		if(current.y < 0)
			current.y = 0;

		current.time = _this.tracking;

		if(!_this.active)
			_this.active = current;
		else
		{
			if(
				_this.active.x >= (current.x - 1) && _this.active.x <= (current.x + 1)
				&& _this.active.y >= (current.y - 1) && _this.active.y <= (current.y + 1)
			)
			{
				if((_this.active.time + 20/*2sec*/) <= current.time)
					_this.ShowTooltip();
			}
			else
				_this.active = current;
		}
	};

	this.ShowTooltip = function()
	{
		var old = document.getElementById('user_info_' + _this.USER_ID);
		if(bDisable || old && old.style.display == 'block')
			return;

		var bIE = (BX.browser.IsIE() && !BX.browser.IsIE10());

		if (!BX.type.isPlainObject(this.params))
		{
			this.params = {};
		}

		if (null == _this.DIV && null == _this.ROOT_DIV)
		{
			_this.ROOT_DIV = document.body.appendChild(document.createElement('DIV'));
			_this.ROOT_DIV.style.position = 'absolute';

			BX.ZIndexManager.register(_this.ROOT_DIV);

			_this.DIV = _this.ROOT_DIV.appendChild(document.createElement('DIV'));
			if (bIE)
				_this.DIV.className = 'bx-user-info-shadow-ie';
			else
				_this.DIV.className = 'bx-user-info-shadow';

			_this.DIV.style.width = _this.width + 'px';
			_this.DIV.style.height = _this.height + 'px';
		}

		var left = _this.CoordsLeft;
		var top = _this.CoordsTop + 30;
		var arScroll = BX.GetWindowScrollPos();
		var body = document.body;

		var h_mirror = false;
		var v_mirror = false;

		if((body.clientWidth + arScroll.scrollLeft) < (left + _this.width))
		{
			left = _this.AnchorRight - _this.width;
			h_mirror = true;
		}

		if((top - arScroll.scrollTop) < 0)
		{
			top = _this.AnchorBottom - 5;
			v_mirror = true;
			_this.v_delta = 40;
		}
		else
			_this.v_delta = 0;

		_this.ROOT_DIV.style.left = parseInt(left) + "px";
		_this.ROOT_DIV.style.top = parseInt(top) + "px";

		BX.ZIndexManager.bringToFront(_this.ROOT_DIV);

		BX.bind(BX(_this.ROOT_DIV), "click", BX.eventCancelBubble);

		if (
			this.rootClassName != 'undefined'
			&& this.rootClassName != null
			&& this.rootClassName.length > 0
		)
			_this.ROOT_DIV.className = this.rootClassName;

		if ('' == _this.DIV.innerHTML)
		{
			var url = _this.LOADER +
				(_this.LOADER.indexOf('?') >= 0 ? '&' : '?') +
				'MUL_MODE=INFO&USER_ID=' + _this.USER_ID +
				'&site=' + (BX.message('SITE_ID') || '') +
				(
					typeof _this.params != 'undefined'
					&& typeof _this.params.entityType != 'undefined'
					&& _this.params.entityType.length > 0
						? '&entityType=' + _this.params.entityType
						: ''
				) +
				(
					typeof _this.params != 'undefined'
					&& typeof _this.params.entityId != 'undefined'
					&& parseInt(_this.params.entityId) > 0
						? '&entityId=' + parseInt(_this.params.entityId)
						: ''
				);

			BX.ajax.get(url, _this.InsertData);
			_this.DIV.id = 'user_info_' + _this.USER_ID;

			_this.DIV.innerHTML = '<div class="bx-user-info-wrap">'
				+ '<div class="bx-user-info-leftcolumn">'
					+ '<div class="bx-user-photo" id="user-info-photo-' + _this.USER_ID + '"><div class="bx-user-info-data-loading">' + BX.message('JS_CORE_LOADING') + '</div></div>'
					+ '<div class="bx-user-tb-control bx-user-tb-control-left" id="user-info-toolbar-' + _this.USER_ID + '"></div>'
				+ '</div>'
				+ '<div class="bx-user-info-data">'
					+ '<div id="user-info-data-card-' + _this.USER_ID + '"></div>'
					+ '<div class="bx-user-info-data-tools">'
						+ '<div class="bx-user-tb-control bx-user-tb-control-right" id="user-info-toolbar2-' + _this.USER_ID + '"></div>'
						+ '<div class="bx-user-info-data-clear"></div>'
					+ '</div>'
				+ '</div>'
				+ '</div><div class="bx-user-info-bottomarea"></div>';
		}

		if (bIE)
		{
			_this.DIV.className = 'bx-user-info-shadow-ie';
			_this.classNameAnim = 'bx-user-info-shadow-anim-ie';
			_this.classNameFixed = 'bx-user-info-shadow-ie';
		}
		else
		{
			_this.DIV.className = 'bx-user-info-shadow';
			_this.classNameAnim = 'bx-user-info-shadow-anim';
			_this.classNameFixed = 'bx-user-info-shadow';
		}

		_this.filterFixed = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/bitrix/components/bitrix/main.user.link/templates/.default/images/cloud-left-top.png', sizingMethod = 'crop' );";

		if (h_mirror && v_mirror)
		{
			if (BX.browser.IsIE6())
			{
				_this.DIV.className = 'bx-user-info-shadow-hv-ie6';
				_this.classNameAnim = 'bx-user-info-shadow-hv-anim-ie6';
				_this.classNameFixed = 'bx-user-info-shadow-hv-ie6';
			}
			else if (bIE)
			{
				_this.DIV.className = 'bx-user-info-shadow-hv-ie';
				_this.classNameAnim = 'bx-user-info-shadow-hv-anim-ie';
				_this.classNameFixed = 'bx-user-info-shadow-hv-ie';
			}
			else
			{
				_this.DIV.className = 'bx-user-info-shadow-hv';
				_this.classNameAnim = 'bx-user-info-shadow-hv-anim';
				_this.classNameFixed = 'bx-user-info-shadow-hv';
			}

			_this.filterFixed = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/bitrix/components/bitrix/main.user.link/templates/.default/images/cloud-right-bottom.png', sizingMethod = 'crop' );";
		}
		else
		{
			if (h_mirror)
			{
				if (bIE)
				{
					_this.DIV.className = 'bx-user-info-shadow-h-ie';
					_this.classNameAnim = 'bx-user-info-shadow-h-anim-ie';
					_this.classNameFixed = 'bx-user-info-shadow-h-ie';
				}
				else
				{
					_this.DIV.className = 'bx-user-info-shadow-h';
					_this.classNameAnim = 'bx-user-info-shadow-h-anim';
					_this.classNameFixed = 'bx-user-info-shadow-h';
				}

				_this.filterFixed = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/bitrix/components/bitrix/main.user.link/templates/.default/images/cloud-right-top.png', sizingMethod = 'crop' );";
			}

			if (v_mirror)
			{
				if (BX.browser.IsIE6())
				{
					_this.DIV.className = 'bx-user-info-shadow-v-ie6';
					_this.classNameAnim = 'bx-user-info-shadow-v-anim-ie6';
					_this.classNameFixed = 'bx-user-info-shadow-v-ie6';
				}
				else if (bIE)
				{
					_this.DIV.className = 'bx-user-info-shadow-v-ie';
					_this.classNameAnim = 'bx-user-info-shadow-v-anim-ie';
					_this.classNameFixed = 'bx-user-info-shadow-v-ie';
				}
				else
				{
					_this.DIV.className = 'bx-user-info-shadow-v';
					_this.classNameAnim = 'bx-user-info-shadow-v-anim';
					_this.classNameFixed = 'bx-user-info-shadow-v';
				}

				_this.filterFixed = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/bitrix/components/bitrix/main.user.link/templates/.default/images/cloud-left-bottom.png', sizingMethod = 'crop' );";
			}
		}


		if (BX.browser.IsIE() && null == _this.IFRAME)
		{
			_this.IFRAME = document.body.appendChild(document.createElement('IFRAME'));
			_this.IFRAME.id = _this.DIV.id + "_frame";
			_this.IFRAME.style.position = 'absolute';
			_this.IFRAME.style.width = (_this.width - 60) + 'px';
			_this.IFRAME.style.height = (_this.height - 100) + 'px';
			_this.IFRAME.style.borderStyle = 'solid';
			_this.IFRAME.style.borderWidth = '0px';
			_this.IFRAME.style.zIndex = 550;
			_this.IFRAME.style.display = 'none';
		}
		if (BX.browser.IsIE())
		{
			_this.IFRAME.style.left = (parseInt(left) + 25) + "px";
			_this.IFRAME.style.top = (parseInt(top) + 30 + _this.v_delta) + "px";
		}

		_this.DIV.style.display = 'none';
		_this.ShowOpacityEffect({func: _this.SetVisible, obj: _this.DIV, arParams: []}, 0);

		document.getElementById('user_info_' + _this.USER_ID).onmouseover = function() {
			_this.StartTrackMouse(this);
		};

		document.getElementById('user_info_' + _this.USER_ID).onmouseout = function() {
			_this.StopTrackMouse(this);
		};

		BX.onCustomEvent('onTooltipShow', [this]);
	};

	this.InsertData = function(data)
	{
		if (null != data && data.length > 0)
		{
			eval('_this.INFO = ' + data);

			var cardEl = document.getElementById('user-info-data-card-' + _this.USER_ID);
			cardEl.innerHTML = _this.INFO.RESULT.Card;

			var photoEl = document.getElementById('user-info-photo-' + _this.USER_ID);
			photoEl.innerHTML = _this.INFO.RESULT.Photo;

			var toolbarEl = document.getElementById('user-info-toolbar-' + _this.USER_ID);
			toolbarEl.innerHTML = _this.INFO.RESULT.Toolbar;

			var toolbar2El = document.getElementById('user-info-toolbar2-' + _this.USER_ID);
			toolbar2El.innerHTML = _this.INFO.RESULT.Toolbar2;

			if(BX.type.isArray(_this.INFO.RESULT.Scripts))
			{
				for(var i = 0; i < _this.INFO.RESULT.Scripts.length; i++)
				{
					eval(_this.INFO.RESULT.Scripts[i]);
				}
			}

			BX.onCustomEvent('onTooltipInsertData', [_this]);
		}
	}

};
BX.CTooltip.prototype.StartTrackMouse = function(ob)
{
	var _this = this;

	if(!this.tracking)
	{
		var elCoords = BX.pos(ob);
		this.RealAnchor = ob;
		this.CoordsLeft = elCoords.left + 0;
		this.CoordsTop = elCoords.top - 325;
		this.AnchorRight = elCoords.right;
		this.AnchorBottom = elCoords.bottom;

		this.tracking = 1;
		BX.bind(document, "mousemove", _this.TrackMouse);

		setTimeout(function() {_this.tickTimer()}, 500);
	}
};

BX.CTooltip.prototype.StopTrackMouse = function()
{
	var _this = this;
	if(this.tracking)
	{
		BX.unbind(document, "mousemove", _this.TrackMouse);
		this.active = false;
		setTimeout(function() {_this.HideTooltip()}, 500);
		this.tracking = false;
	}
};

BX.CTooltip.prototype.tickTimer = function()
{
	var _this = this;

	if(this.tracking)
	{
		this.tracking++;
		if(this.active)
		{
			if( (this.active.time + 5/*0.5sec*/)  <= this.tracking)
				this.ShowTooltip();
		}
		setTimeout(function() {_this.tickTimer()}, 100);
	}
};

BX.CTooltip.prototype.HideTooltip = function()
{
	if(!this.tracking)
		this.ShowOpacityEffect({func: this.SetInVisible, obj: this.DIV, arParams: []}, 1);
};

BX.CTooltip.prototype.ShowOpacityEffect = function(oCallback, bFade)
{
	var steps = 3;
	var period = 1;
	var delta = 1 / steps;
	var i = 0, op, _this = this;

	if(BX.browser.IsIE() && _this.DIV)
		_this.DIV.className = _this.classNameAnim;

	var show = function()
	{
		i++;
		if (i > steps)
		{
			clearInterval(intId);
			if (!oCallback.arParams)
				oCallback.arParams = [];
			if (oCallback.func && oCallback.obj)
				oCallback.func.apply(oCallback.obj, oCallback.arParams);
			return;
		}
		op = bFade ? 1 - i * delta : i * delta;

		if (_this.DIV != null)
		{
			try{
				_this.DIV.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + (op * 100) + ')';
				_this.DIV.style.opacity = op;
				_this.DIV.style.MozOpacity = op;
				_this.DIV.style.KhtmlOpacity = op;
			}
			catch(e){
			}
			finally{
				if (!bFade && i == 1)
					_this.DIV.style.display = 'block';

				if (bFade && i == steps && _this.DIV)
					_this.DIV.style.display = 'none';


				if (BX.browser.IsIE() && i == 1 && bFade && _this.IFRAME)
					_this.IFRAME.style.display = 'none';


				if (BX.browser.IsIE() && i == steps && _this.DIV)
				{
					if (!bFade)
						_this.IFRAME.style.display = 'block';

					_this.DIV.style.filter = _this.filterFixed;
					_this.DIV.className = _this.classNameFixed;
					_this.DIV.innerHTML = ''+_this.DIV.innerHTML;
				}

				if(bFade)
				{
					BX.onCustomEvent('onTooltipHide', [_this]);
				}
			}
		}

	};
	var intId = setInterval(show, period);

}

})(window);

/* End */
;
; /* Start:"a:4:{s:4:"full";s:48:"/bitrix/js/main/rating_like.min.js?1745563966259";s:6:"source";s:30:"/bitrix/js/main/rating_like.js";s:3:"min";s:34:"/bitrix/js/main/rating_like.min.js";s:3:"map";s:34:"/bitrix/js/main/rating_like.map.js";}"*/
if(BX.Type.isUndefined(window.frameCacheVars)){BX.Event.ready((()=>{BX.Runtime.loadExtension("main.rating")}))}else{BX.Event.EventEmitter.subscribe("onFrameDataReceived",(()=>{BX.Runtime.loadExtension("main.rating")}))}
/* End */
;
; /* Start:"a:4:{s:4:"full";s:51:"/bitrix/js/main/core/core_uf.min.js?174556396711652";s:6:"source";s:31:"/bitrix/js/main/core/core_uf.js";s:3:"min";s:35:"/bitrix/js/main/core/core_uf.min.js";s:3:"map";s:35:"/bitrix/js/main/core/core_uf.map.js";}"*/
(function(){"use strict";BX.namespace("BX.Main.UF");if(typeof BX.Main.UF.Manager!=="undefined"){return}var e={};BX.Main.UF.Manager=function(){this.mode=this.mode||"";this.ajaxUrl="/bitrix/tools/uf.php"};BX.Main.UF.Manager.getEdit=function(e,t){return BX.Main.UF.EditManager.get(e,t)};BX.Main.UF.Manager.getView=function(e,t){return BX.Main.UF.ViewManager.get(e,t)};BX.Main.UF.Manager.prototype.get=function(e,t){if(!this.mode){this.displayError(["No mode set. Use BX.UF.EditManager or BX.UF.ViewManager"]);return}return this.query(this.mode,{FIELDS:e.FIELDS,FORM:e.FORM||"",CONTEXT:e.CONTEXT||"",MEDIA_TYPE:e.MEDIA_TYPE||""},t)};BX.Main.UF.Manager.prototype.add=function(e,t){if(!this.mode){this.displayError(["No mode set. Use BX.UF.EditManager or BX.UF.ViewManager"]);return}return this.query(this.mode,{action:"add",FIELDS:e.FIELDS,FORM:e.FORM||""},t)};BX.Main.UF.Manager.prototype.update=function(e,t){if(!this.mode){this.displayError(["No mode set. Use BX.UF.EditManager or BX.UF.ViewManager"]);return}return this.query(this.mode,{action:"update",FIELDS:e.FIELDS,FORM:e.FORM||""},t)};BX.Main.UF.Manager.prototype.delete=function(e,t){if(!this.mode){this.displayError(["No mode set. Use BX.UF.EditManager or BX.UF.ViewManager"]);return}return this.query(this.mode,{action:"delete",FIELDS:e.FIELDS,FORM:e.FORM||""},t)};BX.Main.UF.Manager.prototype.query=function(e,t,n){BX.ajax({dataType:"json",url:this.ajaxUrl,method:"POST",data:this.prepareQuery(e,t),onsuccess:this.queryCallback(n)})};BX.Main.UF.Manager.prototype.prepareQuery=function(e,t){var n=t||{};n.mode=e;n.lang=BX.message("LANGUAGE_ID")||"";n.tpl=BX.message("UF_SITE_TPL")||"";n.tpls=BX.message("UF_SITE_TPL_SIGN")||"";n.sessid=BX.bitrix_sessid();return n};BX.Main.UF.Manager.prototype.queryCallback=function(e){var t=BX.proxy(this.processResult,this);return function(n){t(n,e)}};BX.Main.UF.Manager.prototype.processResult=function(e,t){var n="";if(BX.type.isArray(e.ASSET)){n+=e.ASSET.join("\n")}if(!!e.ERROR){this.displayError(e.ERROR)}return BX.html(null,n).then((function(){if(!!t){t(e.FIELD)}}))};BX.Main.UF.Manager.prototype.displayError=function(e){for(var t in e){if(e.hasOwnProperty(t)){console.error(e[t])}}};BX.Main.UF.Manager.prototype.registerField=function(t,n,i){e[t]={FIELD:n,NODE:i}};BX.Main.UF.Manager.prototype.unRegisterField=function(t){if(!!e[t]){delete e[t]}};BX.Main.UF.ViewManager=function(){BX.Main.UF.ViewManager.superclass.constructor.apply(this,arguments);this.mode="main.view"};BX.extend(BX.Main.UF.ViewManager,BX.Main.UF.Manager);BX.Main.UF.EditManager=function(){BX.Main.UF.EditManager.superclass.constructor.apply(this,arguments);this.mode="main.edit"};BX.extend(BX.Main.UF.EditManager,BX.Main.UF.Manager);BX.Main.UF.EditManager.prototype.validate=function(t,n){if(t.length>0){var i=[];for(var a=0;a<t.length;a++){var r=BX.Main.UF.Factory.getValue(t[a]);if(r!==null){i.push({ENTITY_ID:e[t[a]].FIELD.ENTITY_ID,FIELD:e[t[a]].FIELD.FIELD,ENTITY_VALUE_ID:e[t[a]].FIELD.ENTITY_VALUE_ID,VALUE:r})}}return this.query(this.mode,{action:"validate",FIELDS:i},n)}else{this.queryCallback(n)({FIELD:[]})}};BX.Main.UF.BaseType=function(){};BX.Main.UF.BaseType.prototype.addRow=function(e,t){var n=t.parentNode.getElementsByTagName("span");if(n&&n.length>0&&n[0]){var i=n[0].parentNode;var a=this.getClone(n[n.length-1],e);if(i===t.parentNode){i.insertBefore(a,t)}else{i.appendChild(a)}}};BX.Main.UF.BaseType.prototype.addMobileRow=function(e,t){var n=t.parentNode.getElementsByTagName("span");if(n&&n.length&&n[0]){var i=n[0].parentNode;var a=this.getClone(n[n.length-1],e);var r=a.firstElementChild;var o=r.getAttribute("name");var s=/\[(\d)]/;var p=o.replace(s,(function(e,t){t=parseInt(t)+1;return"["+t+"]"}));var l=false;var F=false;var u=null;r.setAttribute("name",p);if(r.hasChildNodes()){r.childNodes.forEach((function(e,t,n){if(!F&&e.attributes!==undefined&&e.tagName==="INPUT"){e.setAttribute("name",p);F=e.getAttribute("id");l=F+"_1";u=e.getAttribute("data-user-field-type-name")}if(F&&e.attributes!==undefined&&e.id!==undefined){var i=e.getAttribute("id");if(i!==F){e.setAttribute("id",i.replace(F,l))}else{e.setAttribute("id",l)}}}))}if(i===t.parentNode){i.insertBefore(a,t)}else{i.appendChild(a)}if(l){BX.onCustomEvent("onAddMobileUfField",[l,u])}}};BX.Main.UF.BaseType.prototype.getClone=function(e,t){var n=e.cloneNode(true);var i=this.findInput(n,t);for(var a=0;a<i.length;a++){i[a].value=""}return n};BX.Main.UF.BaseType.prototype.findInput=function(e,t){return BX.findChildren(e,{tagName:/INPUT|TEXTAREA|SELECT/i,attribute:{name:t}},true)};BX.Main.UF.BaseType.prototype.isEmpty=function(t){var n=this.getNode(t),i=t+(e[t].FIELD.MULTIPLE==="Y"?"[]":"");if(!BX.isNodeInDom(n)){console.error("Node for field "+t+" is already removed from DOM")}var a=this.findInput(n,i);if(a.length<=0){console.error("Unable to find field "+t+" in the registered node")}else{for(var r=0;r<a.length;r++){if(a[r].value!==""){return false}}}return true};BX.Main.UF.BaseType.prototype.getValue=function(t){var n=this.getNode(t),i=t+(e[t].FIELD.MULTIPLE==="Y"?"[]":""),a=e[t].FIELD.MULTIPLE==="Y"?[]:"";if(!BX.isNodeInDom(n)){console.error("Node for field "+t+" is already removed from DOM")}var r=this.findInput(n,i);if(r.length<=0){var o=n.children.length?n.children[0]:false;if(!BX.util.in_array(e[t].FIELD.USER_TYPE_ID,["crm","employee"])&&(!o||o.getAttribute("data-has-input")!=="no")){console.error("Unable to find field "+t+" in the registered node")}}else{for(var s=0;s<r.length;s++){if(r[s].tagName==="INPUT"&&(r[s].type==="radio"||r[s].type==="checkbox")&&!r[s].checked){continue}if(!BX.Type.isStringFilled(r[s].value)){continue}if(e[t].FIELD.MULTIPLE==="Y"){a.push(r[s].value)}else{a=r[s].value;break}}}return a};BX.Main.UF.BaseType.prototype.focus=function(t){var n=this.getNode(t),i=t+(e[t].FIELD.MULTIPLE==="Y"?"[]":"");if(!BX.isNodeInDom(n)){console.error("Node for field "+t+" is already removed from DOM")}var a=this.findInput(n,i);if(a.length>0){BX.focus(a[0])}};BX.Main.UF.BaseType.prototype.getNode=function(t){return e[t].NODE};BX.Main.UF.TypeBoolean=function(){};BX.extend(BX.Main.UF.TypeBoolean,BX.Main.UF.BaseType);BX.Main.UF.TypeBoolean.USER_TYPE_ID="boolean";BX.Main.UF.TypeBoolean.prototype.isEmpty=function(e){return false};BX.Main.UF.TypeInteger=function(){};BX.extend(BX.Main.UF.TypeInteger,BX.Main.UF.BaseType);BX.Main.UF.TypeInteger.USER_TYPE_ID="integer";BX.Main.UF.TypeDouble=function(){};BX.extend(BX.Main.UF.TypeDouble,BX.Main.UF.BaseType);BX.Main.UF.TypeDouble.USER_TYPE_ID="double";BX.Main.UF.TypeSting=function(){};BX.extend(BX.Main.UF.TypeSting,BX.Main.UF.BaseType);BX.Main.UF.TypeSting.USER_TYPE_ID="string";BX.Main.UF.TypeUrl=function(){};BX.extend(BX.Main.UF.TypeUrl,BX.Main.UF.BaseType);BX.Main.UF.TypeUrl.USER_TYPE_ID="url";BX.Main.UF.TypeStingFormatted=function(){};BX.extend(BX.Main.UF.TypeStingFormatted,BX.Main.UF.TypeSting);BX.Main.UF.TypeStingFormatted.USER_TYPE_ID="string_formatted";BX.Main.UF.TypeEnumeration=function(){};BX.extend(BX.Main.UF.TypeEnumeration,BX.Main.UF.BaseType);BX.Main.UF.TypeEnumeration.USER_TYPE_ID="enumeration";BX.Main.UF.TypeEnumeration.prototype.findInput=function(e,t){var n=BX.Main.UF.TypeEnumeration.superclass.findInput.apply(this,arguments);if(n.length>0){for(var i=0;i<n.length;i++){if(n[i].tagName==="INPUT"&&n[i].type==="hidden"&&n.length>1){delete n[i];break}}}return BX.util.array_values(n)};BX.Main.UF.TypeEnumeration.prototype.focus=function(t){if(e[t]&&e[t].FIELD.SETTINGS.DISPLAY==="UI"&&BX.type.isElementNode(e[t].NODE)){BX.fireEvent(e[t].NODE,"focus")}else{BX.Main.UF.TypeEnumeration.superclass.focus.apply(this,arguments)}};BX.Main.UF.TypeDate=function(){};BX.extend(BX.Main.UF.TypeDate,BX.Main.UF.BaseType);BX.Main.UF.TypeDate.USER_TYPE_ID="date";BX.Main.UF.TypeDate.prototype.focus=function(t){var n=t+(e[t].FIELD.MULTIPLE==="Y"?"[]":"");var i=this.findInput(this.getNode(t),n);if(i.length>0){BX.fireEvent(i[0],"click")}BX.Main.UF.TypeDate.superclass.focus.apply(this,arguments)};BX.Main.UF.TypeDateTime=function(){};BX.extend(BX.Main.UF.TypeDateTime,BX.Main.UF.TypeDate);BX.Main.UF.TypeDateTime.USER_TYPE_ID="datetime";BX.Main.UF.TypeFile=function(){};BX.extend(BX.Main.UF.TypeFile,BX.Main.UF.BaseType);BX.Main.UF.TypeFile.USER_TYPE_ID="file";BX.Main.UF.TypeFile.prototype.findInput=function(e,t){var n=BX.Main.UF.TypeFile.superclass.findInput.apply(this,arguments);if(n.length<=0){n=BX.findChildren(e,{tagName:/INPUT/i,attribute:{type:"file",name:/^bxu_files/}},true)}return n};BX.Main.UF.TypeFile.prototype.getValue=function(t){var n=BX.Main.UF.TypeFile.superclass.getValue.apply(this,arguments),i=e[t].NODE,a=[],r;if(e[t].FIELD.MULTIPLE==="Y"){var o=t+"_del[]";if(BX.type.isArray(n)&&n.length>0){a=BX.Main.UF.TypeFile.superclass.findInput.apply(this,[i,o]);for(r=0;r<a.length;r++){var s=BX.util.array_search(a[r].value,n);if(s>=0){n[s]={old_id:a[r].value,del:"Y",tmp_name:""}}}}return BX.util.array_values(n)}if(n>0){var o=t+"_del";a=BX.Main.UF.TypeFile.superclass.findInput.apply(this,[i,o]);for(r=0;r<a.length;r++){if(n==a[r].value){n={old_id:n,del:"Y",tmp_name:""};break}}return n}return null};BX.Main.UF.Factory=function(){this.defaultTypeHandler=BX.Main.UF.BaseType;this.typeHandlerList={};this.objectCollection={}};BX.Main.UF.Factory.prototype.setTypeHandler=function(e,t){this.typeHandlerList[e]=t;if(typeof this.objectCollection[e]!=="undefined"){delete this.objectCollection[e]}};BX.Main.UF.Factory.prototype.get=function(e){if(typeof this.objectCollection[e]==="undefined"){this.objectCollection[e]=this.getObject(e)}return this.objectCollection[e]};BX.Main.UF.Factory.prototype.getObject=function(e){return new(this.typeHandlerList[e]||this.defaultTypeHandler)};BX.Main.UF.Factory.prototype.getFieldObject=function(t){if(typeof e[t]==="undefined"){console.error("Field "+t+"is not registered. Use BX.Main.UF.Factory.registerField to register");return null}return this.get(e[t]["FIELD"]["USER_TYPE_ID"])};BX.Main.UF.Factory.prototype.isEmpty=function(t){if(typeof e[t]==="undefined"){console.error("Field "+t+"is not registered. Use BX.Main.UF.Factory.registerField to register");return true}return this.get(e[t]["FIELD"]["USER_TYPE_ID"]).isEmpty(t)};BX.Main.UF.Factory.prototype.getValue=function(t){if(typeof e[t]==="undefined"){console.error("Field "+t+"is not registered. Use BX.Main.UF.Factory.registerField to register");return null}return this.get(e[t]["FIELD"]["USER_TYPE_ID"]).getValue(t)};BX.Main.UF.Factory.prototype.focus=function(t){if(typeof e[t]==="undefined"){console.error("Field "+t+"is not registered. Use BX.Main.UF.Factory.registerField to register")}return this.get(e[t]["FIELD"]["USER_TYPE_ID"]).focus(t)};BX.Main.UF.EditManager=new BX.Main.UF.EditManager;BX.Main.UF.ViewManager=new BX.Main.UF.ViewManager;BX.Main.UF.Factory=new BX.Main.UF.Factory;BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeBoolean.USER_TYPE_ID,BX.Main.UF.TypeBoolean);BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeInteger.USER_TYPE_ID,BX.Main.UF.TypeInteger);BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeDouble.USER_TYPE_ID,BX.Main.UF.TypeDouble);BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeSting.USER_TYPE_ID,BX.Main.UF.TypeSting);BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeStingFormatted.USER_TYPE_ID,BX.Main.UF.TypeStingFormatted);BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeEnumeration.USER_TYPE_ID,BX.Main.UF.TypeEnumeration);BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeFile.USER_TYPE_ID,BX.Main.UF.TypeFile);BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeDate.USER_TYPE_ID,BX.Main.UF.TypeDate);BX.Main.UF.Factory.setTypeHandler(BX.Main.UF.TypeDateTime.USER_TYPE_ID,BX.Main.UF.TypeDateTime)})();
/* End */
;
; /* Start:"a:4:{s:4:"full";s:52:"/bitrix/js/main/core/core_autosave.js?17455639679741";s:6:"source";s:37:"/bitrix/js/main/core/core_autosave.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function(window){
	var topWindow = BX.PageObject.getRootWindow();
if (BX.CAutoSave && topWindow.BX.CAutoSave) return;
/******************************* AUTOSAVE *********************************/

BX.CAutoSave = function(params)
{
	this.FORM_NAME = params.form;
	this.FORM_MARKER = params.form_marker;
	this.FORM_ID = params.form_id;

	this.PERIOD = params.period || [4001, 20990];

	this.RESTORE_DATA = null;
	this.TIMERS = [null, null];

	this.bInited = false;
	this.bRestoreInProgress = false;

	this.DISABLE_STANDARD_NOTIFY = params.DISABLE_STANDARD_NOTIFY;
	this.NOTIFY_CONTEXT = null;

	BX.ready(BX.defer(this.Prepare, this));
	BX.garbage(BX.delegate(this.Clear, this));

	if (
		BX.type.isNotEmptyString(this.FORM_MARKER)
		&& BX(this.FORM_MARKER)
	)
	{
		var formMarker = BX(this.FORM_MARKER);
		if (
			BX(formMarker.form)
			&& BX.type.isNotEmptyString(formMarker.form.name)
		)
		{
			BX.addCustomEvent(topWindow, 'onExtAutoSaveReset_' + formMarker.form.name, BX.proxy(this.Reset, this));
		}
	}
};

BX.CAutoSave.prototype.Prepare = function()
{
	var i;

	if (this.FORM_NAME && BX.type.isString(this.FORM_NAME))
		this.FORM = document.forms[this.FORM_NAME];
	else if (this.FORM_MARKER && BX.type.isString(this.FORM_MARKER))
		this.FORM = (BX(this.FORM_MARKER)||{form:null}).form;

	if (!BX.type.isDomNode(this.FORM))
		return;

	this.FORM.BXAUTOSAVE = this;
	BX.bind(this.FORM, 'submit', BX.proxy(this.ClearTimers, this));

	for (i=0; i<this.FORM.elements.length; i++)
	{
		this.RegisterInput(this.FORM.elements[i]);
	}

	setTimeout(BX.delegate(this._PrepareAfter, this), 10);
};

BX.CAutoSave.prototype.RegisterInput = function(inp)
{
	if (BX.type.isString(inp))
	{
		setTimeout(BX.delegate(function(){this.RegisterInput(this.FORM[inp] || BX(inp))}, this), 10);
	}
	else if (BX.type.isDomNode(inp))
	{
		if (
			inp.type != 'button'
			&& inp.type != 'submit'
			&& inp.type != 'reset'
			&& inp.type != 'image'
			&& inp.type != 'hidden'
		)
		{
			BX.bind(inp, 'change', BX.proxy(this.Init, this));

			if (inp.type == 'text' || inp.type == 'textarea')
			{
				BX.bind(inp, 'keyup', BX.proxy(this.Init, this));
			}

			if (inp.type == 'checkbox' || inp.type == 'radio')
			{
				BX.bind(inp, 'click', BX.proxy(this.Init, this));
			}
		}
	}
};

BX.CAutoSave.prototype.UnRegisterInput = function(inp)
{
	if (BX.type.isString(inp))
		inp = this.FORM[inp] || BX(inp);
	if (BX.type.isDomNode(inp))
	{
		BX.unbind(inp, 'change', BX.proxy(this.Init, this));
		BX.unbind(inp, 'keyup', BX.proxy(this.Init, this));
		BX.unbind(inp, 'click', BX.proxy(this.Init, this));
	}
};

BX.CAutoSave.prototype._PrepareAfter = function()
{
	// we can set other "target events" here
	BX.onCustomEvent(this.FORM, 'onAutoSavePrepare', [this, BX.proxy(this.Init, this)]);

	if (this.RESTORE_DATA)
	{
		var id = this.FORM.name || Math.random();
		BX.removeCustomEvent('onExtAutoSaveRestoreClick_' + id, BX.proxy(this.Restore, this));
		BX.addCustomEvent('onExtAutoSaveRestoreClick_' + id, BX.proxy(this.Restore, this));

		var o = this._NotifyContext();
		if (o)
		{
			o.Notify(
				BX.message('AUTOSAVE') + ' <a href="javascript:void(0)" onclick="BX.CAutoSave.Restore(\'' + BX.util.urlencode(id) + '\', this); return false;">' +	BX.message('AUTOSAVE_R') + '</a>',
				false,
				true
			);
		}

		// may be useful sometimes
		BX.onCustomEvent(this.FORM, 'onAutoSaveRestoreFound', [this, this.RESTORE_DATA]);
	}
};

BX.CAutoSave.prototype.Init = function()
{
	// if (this.bRestoreInProgress)
		// return;

	if (this.TIMERS[0])
	{
		clearTimeout(this.TIMERS[0]);
		this.TIMERS[0] = null;
	}

	this.TIMERS[0] = setTimeout(BX.proxy(this.TimerHandler, this), this.PERIOD[0]);

	if (!this.TIMERS[1])
	{
		this.TIMERS[1] = setInterval(BX.proxy(this.Save, this), this.PERIOD[1]);
	}

	// may also be useful
	BX.onCustomEvent(this.FORM, 'onAutoSaveInit', [this]);

	return true;
};

BX.CAutoSave.prototype.TimerHandler = function()
{
	if (this.TIMERS[1])
	{
		clearInterval(this.TIMERS[1]);
		this.TIMERS[1] = null;
	}
	this.Save();
};

BX.CAutoSave.prototype.Save = function()
{
	if (this.FORM && BX.isNodeInDom(this.FORM))
	{
		var i, j, el, data = {autosave_id: this.FORM_ID, form_data: {}};

		for (i=0; i<this.FORM.elements.length; i++)
		{
			el = this.FORM.elements[i];

			if (el.name && el.name != 'sessid' && el.name != 'lang' && el.name != 'autosave_id')
			{
				var n = el.name, v = '', t = el.type.toLowerCase();

				switch (t)
				{
					case 'button':
					case 'submit':
					case 'reset':
					case 'image':
					case 'file':
					case 'password':
						break;

					case 'radio':
					case 'checkbox':
						if (el.checked)
							v = el.value || 'on';
					break;

					case 'select-multiple':
						n = n.substring(0, n.length-2);
						v = [];
						for (j=0;j<el.options.length;j++)
						{
							if (el.options[j].selected)
							{
								v.push(el.options[j].value);
							}
						}
					break;

					default:
						v = el.value;
				}

				if (n.indexOf('[]') > 0)
				{
					n = _encodeName(n);
					if (typeof(data.form_data[n]) == 'undefined')
						data.form_data[n] = [v];
					else
						data.form_data[n].push(v);
				}
				else
					data.form_data[_encodeName(n)] = v;
			}
		}

		// we can adjust form_data before autosaving
		BX.onCustomEvent(this.FORM, 'onAutoSave', [this, data.form_data]);
		BX.ajax.post(
			'/bitrix/tools/autosave.php?bxsender=core_autosave&sessid=' + BX.bitrix_sessid(), data, BX.proxy(this._Save, this)
		);
	}
	else
	{
		this.Clear();
	}
};

BX.CAutoSave.prototype.Reset = function()
{
	if (this.FORM && BX.isNodeInDom(this.FORM))
	{
		BX.ajax.post(
			'/bitrix/tools/autosave.php?bxsender=core_autosave&action=reset&sessid=' + BX.bitrix_sessid(), {autosave_id: this.FORM_ID }, null
		);
	}
};

BX.CAutoSave.prototype._Save = function(data)
{
	BX.onCustomEvent(this.FORM, 'onAutoSaveFinished', [this, data]);
};

BX.CAutoSave.prototype.Restore = function(data, clicker)
{
	if (data)
	{
		this.RESTORE_DATA = _decodeData(data);
	}
	else if (this.FORM && this.RESTORE_DATA)
	{
		// we can change restore data or make some unusual actions here
		BX.onCustomEvent(this.FORM, 'onAutoSaveRestore', [this, this.RESTORE_DATA]);

		this.bRestoreInProgress = true;

		for (var i=0; i<this.FORM.elements.length; i++)
		{
			var el = this.FORM.elements[i];
			if (el && BX.type.isDomNode(el) && el.name)
			{
				var value = undefined, n = el.name;

				if (el.type == 'select-multiple')
					n = el.name.substring(0, el.name.length-2);

				value = this.RESTORE_DATA[n];

				if (n.indexOf('[]') > 0 && BX.type.isArray(value))
					value = this.RESTORE_DATA[n].shift();

				if (el.type != 'checkbox' && typeof value == 'undefined')
					continue;

				var bChange = false;

				switch(el.type)
				{
					case 'radio':
						if (!el.checked && !!(value == el.value))
						{
							bChange = true;
							BX.fireEvent(el, 'click');
						}
					break;
					case 'checkbox':
						if (el.checked != !!(value == el.value))
						{
							bChange = true;
							BX.fireEvent(el, 'click');
						}
					break;

					case 'select-one':
						for (var j = 0; j < el.options.length; j++)
						{
							var q = el.options[j].selected;
							el.options[j].selected = !!(value == el.options[j].value);
							bChange |= el.options[j].selected != q;
						}

						break;

					case 'select-multiple':
						value = this.RESTORE_DATA[el.name.substring(0, el.name.length-2)];
						for (j = 0; j < el.options.length; j++)
						{
							q = el.options[j].selected;
							el.options[j].selected = !!(BX.type.isArray(value) && BX.util.in_array(el.options[j].value, value));
							bChange |= el.options[j].selected != q;
						}
						break;

					case 'file':
					case 'button':
					case 'image':
					case 'submit':
					case 'reset':
					case 'password':
						break;

					default:
						bChange = value != el.value;
						el.value = value;
				}

				if (bChange)
					BX.fireEvent(el, 'change');
			}
		}

		var o = this._NotifyContext();
		if (o)
			o.hideNotify(clicker.parentNode.parentNode);

		this.bRestoreInProgress = false;

		BX.onCustomEvent(this.FORM, 'onAutoSaveRestoreFinished', [this, this.RESTORE_DATA]);
	}
};

BX.CAutoSave.prototype._NotifyContext = function()
{
	var o = null;

	if (!this.DISABLE_STANDARD_NOTIFY)
	{
		if (this.NOTIFY_CONTEXT)
			o = this.NOTIFY_CONTEXT;
		else if (BX.WindowManager && BX.WindowManager.Get())
			o = BX.WindowManager.Get();
		else if (BX.adminPanel)
			o = BX.adminPanel;
		else if (BX.admin && BX.admin.panel)
			o = BX.admin.panel;

		this.NOTIFY_CONTEXT = o;
	}

	return o;
};

BX.CAutoSave.prototype.ClearTimers = function()
{
	if (this.TIMERS)
	{
		clearTimeout(this.TIMERS[0]);
		clearInterval(this.TIMERS[1]);
	}
};

BX.CAutoSave.prototype.Clear = function()
{
	if (this.FORM)
	{
		this.FORM.BXAUTOSAVE = null;

		for (var i=0; i<this.FORM.elements.length; i++)
		{
			this.UnRegisterInput(this.FORM.elements[i]);
		}
	}

	this.ClearTimers();

	// we should unset any additional "target events" here
	BX.onCustomEvent(this.FORM, 'onAutoSaveClear', [this]);

	this.FORM = null;
	this.TIMERS = null;
};

BX.CAutoSave.Restore = function(id, el)
{
	BX.onCustomEvent('onExtAutoSaveRestoreClick_' + id, [null, el]);
};

function _encodeName(n)
{
	var q;
	while (q = /[^a-zA-Z0-9_\-]/.exec(n))
	{
		n = n.replace(q[0], 'X' + BX.util.str_pad_left(q[0].charCodeAt(0).toString(), 6, '0') + 'X');
	}
	return n;
}

function _decodeName(n)
{
	var q;
	while (q = /X[\d]{6}X/.exec(n))
	{
		n = n.replace(q[0], String.fromCharCode(parseInt(q[0].replace(/(^X[0]*)|(X$)/g, ''))))
	}
	return n;
}

function _decodeData(data)
{
	var d = {};
	for (var i in data)
	{
		d[_decodeName(i)] = data[i];
	}
	return d;
}
	topWindow.BX.CAutoSave = BX.CAutoSave;
})(window);


/* End */
;
//# sourceMappingURL=kernel_main.map.js