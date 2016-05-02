

$(function() {   
var pie = new d3pie("pieChart", {
	"header": {
		"title": {
			"text": $('#graphTitle').val(),
			"fontSize": 14,
			"font": "Trebuchet MS"
		}
	},
	"size": {
		"canvasWidth": 600,
		"canvasHeight": 400,
		"pieInnerRadius": "30%",
		"pieOuterRadius": "70%"
	},
	"data": {
		"sortOrder": "value-desc",
		"content": graphcontent
	},
	"labels": {
		"outer": {
			"pieDistance": 32
		},
		"inner": {
			"hideWhenLessThanPercentage": 3
		},
		"mainLabel": {
			"fontSize": 12
		},
		"percentage": {
			"color": "#ffffff",
			"decimalPlaces": 0
		},
		"value": {
			"color": "#adadad",
			"fontSize": 12
		},
		"lines": {
			"enabled": true
		},
		"truncation": {
			"enabled": false
		}
	},
	"effects": {
		"pullOutSegmentOnClick": {
			"effect": "linear",
			"speed": 400,
			"size": 8
		}
	},
	"misc": {
		"colors": {
			background: "#F7F7F9"
		},
		"gradient": {
			"enabled": true,
			"percentage": 100
		},
		"pieCenterOffset": {
			"y": -5
		},
		"canvasPadding": {
			"top": 5,
			"right": 5,
			"bottom": 5,
			"left": 5
		}
	}
});
});