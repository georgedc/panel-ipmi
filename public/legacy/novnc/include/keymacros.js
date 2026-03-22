
var KeyMacros = {

	createNew: function(clickFunc){
		var keymacros = {};

	    "use strict";
		var KEYMACROS_CGI = "../../cgi/keymacros.cgi";
		var KEYMACROS_XML = "keymacro.xml";
		var KEYMACRO_ID_INDEX = "keymacro_btn_";
		var KEYMACROMENU_UL = "macroMenuUl";
	    var name = "";
	    var value = "";
		keymacros.sendMacro = clickFunc;
		/*
		keymacros.menuBtn = {
			submenu: null,
			itemdata: null,
			defItemFun: null,
			drawBtnFunc: null
		};
		*/

		var KeyMacro = function(){
				var keymacro = {};
				keymacro.name = "";
				keymacro.keys = "";
				keymacro.button = "";
				keymacro.id = "";
				keymacro.keysParsing = "";
				keymacro.keysParsingCodes = "";
				return keymacro;

		};

		//handler for read keyboard macros
		var KeyMacrosArray = new Array();
		var KeyMappingTable = {

			createNew: function() {

				var keymappingtable = {};
				var KeyMapping = function(name, code) {
					var keymapping = {};
					keymapping.name = name;
					keymapping.code = code;
					return keymapping;
				};
				var KeyMappingArray = new Array();

				var addKeyMapping = function(name, code) {
					var keymapping = KeyMapping(name, code);
					KeyMappingArray.push(keymapping);
				};
				var initKeyMapping = function() {
					KeyMappingArray = new Array();
				};

				var printKeyMappingTable = function() {
					var i;
					for (i=0; i<KeyMappingArray.length; i++){
						Util.Debug(i + ":"
									+ "name: " + KeyMappingArray[i].name
									+ ", code: " + KeyMappingArray[i].code.toString(16));
					}
				};

				var toLowerCase = function() {
					var i;
					for (i=0; i<KeyMappingArray.length; i++) {
						KeyMappingArray[i].name = KeyMappingArray[i].name.toLocaleLowerCase();
					}
				}

				var searchCodeByName = function(name) {
					var i;
					var nameLower = name.toLocaleLowerCase();
					for (i=0; i<KeyMappingArray.length; i++) {
						if (nameLower.localeCompare(KeyMappingArray[i].name) == 0 ) {
							return KeyMappingArray[i].code;
						}
					}


					return 0;
				}

				initKeyMapping();
				keymappingtable.KeyMappingArray = KeyMappingArray;
				keymappingtable.put = addKeyMapping;
				keymappingtable.printTable = printKeyMappingTable;
				keymappingtable.toLowerCase = toLowerCase;
				keymappingtable.searchCodeByName = searchCodeByName;

				return keymappingtable;
			}
		};
		var keycodeTable;

		var initKeycodeTable = function() {
			keycodeTable = KeyMappingTable.createNew();

			keycodeTable.put("SHIFT", XK_Shift_L);
	    	keycodeTable.put("CTRL", XK_Control_L);
	    	keycodeTable.put("ALT", XK_Alt_L);
	    	keycodeTable.put("ALTGR", XK_Alt_L);
	    	keycodeTable.put("META", XK_Meta_L);
	    	
	    	keycodeTable.put("0", XK_0);
	    	keycodeTable.put("1", XK_1);
	    	keycodeTable.put("2", XK_2);
	    	keycodeTable.put("3", XK_3);
	    	keycodeTable.put("4", XK_4);
	    	keycodeTable.put("5", XK_5);
	    	keycodeTable.put("6", XK_6);
	    	keycodeTable.put("7", XK_7);
	    	keycodeTable.put("8", XK_8);
	    	keycodeTable.put("9", XK_9);
	    	keycodeTable.put("F1", XK_F1);
	    	keycodeTable.put("F2", XK_F2);
	    	keycodeTable.put("F3", XK_F3);
	    	keycodeTable.put("F4", XK_F4);
	    	keycodeTable.put("F5", XK_F5);
	    	keycodeTable.put("F6", XK_F6);
	    	keycodeTable.put("F7", XK_F7);
	    	keycodeTable.put("F8", XK_F8);
	    	keycodeTable.put("F9", XK_F9);
	    	keycodeTable.put("F10", XK_F10);
	    	keycodeTable.put("F11", XK_F11);
	    	keycodeTable.put("F12", XK_F12);
	    	keycodeTable.put("A", XK_A);
	    	keycodeTable.put("B", XK_B);
	    	keycodeTable.put("C", XK_C);
	    	keycodeTable.put("D", XK_D);
	    	keycodeTable.put("E", XK_E);
	    	keycodeTable.put("F", XK_F);
	    	keycodeTable.put("G", XK_G);
	    	keycodeTable.put("H", XK_H);
	    	keycodeTable.put("I", XK_I);
	    	keycodeTable.put("J", XK_J);
	    	keycodeTable.put("K", XK_K);
	    	keycodeTable.put("L", XK_L);
	    	keycodeTable.put("M", XK_M);
	    	keycodeTable.put("N", XK_N);
	    	keycodeTable.put("O", XK_O);
	    	keycodeTable.put("P", XK_P);
	    	keycodeTable.put("Q", XK_Q);
	    	keycodeTable.put("R", XK_R);
	    	keycodeTable.put("S", XK_S);
	    	keycodeTable.put("T", XK_T);
	    	keycodeTable.put("U", XK_U);
	    	keycodeTable.put("V", XK_V);
	    	keycodeTable.put("W", XK_W);
	    	keycodeTable.put("X", XK_X);
	    	keycodeTable.put("Y", XK_Y);
	    	keycodeTable.put("Z", XK_Z);
	    	keycodeTable.put("PageUP", XK_Page_Up);
	    	keycodeTable.put("PAGEDOWN", XK_Page_Down);
	    	keycodeTable.put("INSERT", XK_Insert);
	    	keycodeTable.put("INS", XK_Insert);
	    	keycodeTable.put("HOME", XK_Home);
	    	keycodeTable.put("END", XK_End);
	    	keycodeTable.put("DEL", XK_Delete);
	    	keycodeTable.put("DELETE", XK_Delete);
	    	keycodeTable.put("PRTSC", XK_Print);
	    	keycodeTable.put("PrntScn", XK_Print);
	    	keycodeTable.put("PRINTSCREEN", XK_Print);

	    	// Javascript add
	    	keycodeTable.put("Esc", XK_Escape);
	    	keycodeTable.put("Tab", XK_Tab);
	    	keycodeTable.put("Win", XK_Super_L);
	    	keycodeTable.put("Win_R", XK_Super_R);
	    	keycodeTable.put("Win_L", XK_Super_L);
	    	keycodeTable.put("ALT_L", XK_Alt_L);
	    	keycodeTable.put("ALT_R", XK_Alt_R);
	    	keycodeTable.put("Space", XK_space);
	    	keycodeTable.put("Enter", XK_Return);
	    	keycodeTable.put("Backspace", XK_BackSpace);
	    	keycodeTable.put("Pause", XK_Pause);
	    	keycodeTable.put("Hypen", XK_Hyper_L);

	    	keycodeTable.toLowerCase();
	    	// Debug
	    	// keycodeTable.printTable();
		};

		var NameParsing = function(name) {
			var parsing = name.split("+");
			var i;
			for(i=0; i<parsing.length; i++){
				parsing[i] = parsing[i].trim().toLocaleLowerCase();
			}
			// var parsingtrim = parsing.trim();
			return parsing;
		};

		var AddKeysParsing = function(keymacro) {
			
			keymacro.keysParsing = NameParsing(keymacro.keys);

		};
		/*
		var KeyMacroToBtn = function(defItemFun, keymacro) {

			var btn = new Object();

			if (keymacro.name == "") {
				keymacro.name = keymacro.keys;
			}
			// var func = "javascript:page_mapping('macro_defined', '" 
		 //  		+ keymacro.keys 
		 //  		+ "')" ;
			// var btn_index = "macro_defined";
			// btn = {
			// 	text: keymacro.name,
			// 	url: func,
			// 	index: btn_index
			// };
			btn = defItemFun(keymacro.name, keymacro.keys);
		 	return btn;
		};
		
		var ReadKeyMacrosHTML5 = function(originalRequest)
		{
			var KeyMacrosArray = keymacros.KeyMacrosArray;
		    if (originalRequest != null && originalRequest.readyState == 4 && originalRequest.status == 200)
		    {
		      var response = originalRequest.responseText;
		      var xml_obj = GetResponseXML(response);
		        if(xml_obj != null)
		        {
		          var idx = 0;
		          var KEYMACROSRoot = xml_obj.documentElement;
		            var KeyMacros = KEYMACROSRoot.getElementsByTagName('KEYMACRO');//point to KEYMACRO
		            var KeyMacroName;
		            var KeyMacroKeys;
		            var KeyNameObj;
		            var KeyValueObj;
		            var FieldStr;
		            var keyMacroBtns = new Array();
		            var keyMacroBtn;
		            var keyMacroBtnDefItemFun = keymacros.menuBtn.defItemFun;
		            if(KeyMacros != null) {
		                for (idx = 0; idx < KeyMacros.length; idx++) {
		                  var temp = new KeyMacro();
		                  temp.name = KeyMacros[idx].getAttribute("NAME");
		                  temp.keys = KeyMacros[idx].getAttribute("KEYS");
		                  temp.id = KEYMACRO_ID_INDEX + idx;
		                  KeyMacrosArray.push(temp);
		                  // keyMacroBtn = KeyMacroToBtn(KeyMacrosArray[idx]);
		                  if (keyMacroBtnDefItemFun)
		                  	keyMacroBtn = KeyMacroToBtn(keyMacroBtnDefItemFun, KeyMacrosArray[idx]);
		                  keyMacroBtns.push(keyMacroBtn);
		                  // AddKeysParsing(KeyMacrosArray[idx]);
						  // transMacroToXK(KeyMacrosArray[idx]);

		                  // Debug
		                  Util.Debug(
		                  	idx + ": " + 
		                  	KeyMacrosArray[idx].name + ", " + 
		                  	KeyMacrosArray[idx].id + ", " 
		                  	// KeyMacrosArray[idx].keysParsingCodes
		                  	);
		                }
		                if (keymacros.menuBtn.submenu) {
		                	keymacros.menuBtn.submenu.itemdata = keyMacroBtns;
		                	if (keymacros.menuBtn.submenu.itemdata.length > 0)
		                		keymacros.menuBtn.itemdata.disabled = false;
		                }

		            }
		        }
		        if (keymacros.menuBtn.drawBtnFunc)
		        	keymacros.menuBtn.drawBtnFunc();
		        // keymacros.transMacrosToXK();
		        // keymacros.appendMacrosBtnOnclick();

		    }
		}.bind(keymacros);
		
		var ReadKeyMacros = function()
		{
		  var ajax_url = KEYMACROS_CGI;
		  var ajax_param = KEYMACROS_XML;
		  var ajax_req = new Ajax.Request(
		    ajax_url,
		    {method: 'get', parameters: ajax_param, onComplete: ReadKeyMacrosHTML5}//register callback function
		    );
		};
		*/
		var transMacroToArray = function(name)
		{
			var index;
			var res;

			var keysParsing = NameParsing(name);
			var keycodes = new Array();

			for (index = 0; index < keysParsing.length; index++){
				var name = keysParsing[index];
				var code = keycodeTable.searchCodeByName(name);
				if (code == 0) {
					Util.Error("Find Macros: " + keysParsing[index] + " failed !");
					res = false;
					break;
				} else {
					keycodes.push(code);
					res = true;
				}
			}
			if (res)
				res = keycodes;
			else
				res = new Array();
			return res;
		};

		var transMacroToXK = function(keymacro)
		{
			var index;
			var res;

			var keycodes = new Array();

			for (index = 0; index < keymacro.keysParsing.length; index++){
				var name = keymacro.keysParsing[index];
				var code = keycodeTable.searchCodeByName(name);
				if (code == 0) {
					Util.Error("Find Macros: " + keymacro.keysParsing[index] + " failed !");
					res = false;
					break;
				} else {
					keycodes.push(code);
					res = true;
				}
			}
			if (res)
				keymacro.keysParsingCodes = keycodes;
			else 
				keymacro.keysParsingCodes = new Array();
			return res;
		};
		var transMacrosToXK = function() 
		{
			var index;
			var res;
			// initKeycodeTable();

			for (index = 0; index<KeyMacrosArray.length; index++) {
				res = transMacroToXK(KeyMacrosArray[index]);
				if (res == false) {
					Util.Error("transmacrosToXK failed");
					Util.Debug(KeyMacrosArray[index].name + ", "
								+ KeyMacrosArray[index].keys);
					// return false;
				}
			}
			return true;
		};
		/*
		var appendMacroBtnOnclick = function(keymacro) {
			if (keymacro.keysParsingCodes == 0) {
				$D(keymacro.id).value = "Unknown";
				return false;
			}
			else {
				$D(keymacro.id).onclick = function() {
					return sendMacro(keymacro.keysParsingCodes);
				};
				return true;
			}

		};
		var appendMacrosBtnOnclick = function() 
		{
			var index;
			var res;

			for (index = 0; index <KeyMacrosArray.length; index++) {
				res = appendMacroBtnOnclick(KeyMacrosArray[index]);
				if (res = false) {
					Util.Error(KeyMacrosArray[index].name + KeyMacrosArray[index].keys);
					// return false;
				}
			}
			return true;
		};
		
		keymacros.InitMacroBtns = function(itemdata, defItem, drawBtnFunc)
		{
			var res;
			KeyMacrosArray.clear();
			keymacros.menuBtn.itemdata = itemdata;
			keymacros.menuBtn.submenu = itemdata.submenu;
			keymacros.menuBtn.defItemFun = defItem;
			keymacros.menuBtn.drawBtnFunc = drawBtnFunc;
			ReadKeyMacros();
			Util.Debug("InitMacroBtns");
		};*/
		keymacros.transNameToKeymacroArray = function(name)
		{
			var keycode = transMacroToArray(name);
			if (keycode.length == 0) 
			{
				Util.Debug("transNameToKeymacroArray failed: " + name);
				return keycode;
			}
			return keycode;
		};
		keymacros.KeyMacrosArray = KeyMacrosArray;
		keymacros.transMacroToXK = transMacroToXK;
		keymacros.transMacrosToXK = transMacrosToXK;
		//keymacros.appendMacrosBtnOnclick = appendMacrosBtnOnclick;

		initKeycodeTable();
		return keymacros;
	}
};

