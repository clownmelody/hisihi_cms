/*绘制线*/
//普通直线

define(['jquery'], function ($) {
    var DrawLinCanvas = function (canvas) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
    };

    DrawLinCanvas.prototype = {
        contructor: DrawLinCanvas,
        drawNormalLine: function () {
            this.clearCanvas();
            this.ctx.moveTo(20, 20);
            this.ctx.lineTo(150, 150);
            this.ctx.lineTo(210, 340);
            this.ctx.strokeStyle = '#707074';
            this.ctx.stroke();
        },
        drawDiffColorNormalLine: function () {
            this.clearCanvas();
            this.ctx.moveTo(100, 30);
            this.ctx.lineTo(180, 250);
            this.ctx.strokeStyle = 'red';
            this.ctx.stroke();

            this.ctx.beginPath();
            this.ctx.moveTo(40, 40);
            this.ctx.lineTo(400, 200);
            this.ctx.strokeStyle = 'blue';
            this.ctx.stroke();
        },
        drawGradientLine: function () {
            this.clearCanvas();

            var grd = this.ctx.createLinearGradient(0, 0, 170, 0);
            grd.addColorStop(0, 'black');
            grd.addColorStop(0.2, 'red');
            grd.addColorStop(1, 'yellow');

            this.ctx.moveTo(100, 30);
            this.ctx.lineTo(180, 250);
            this.ctx.lineTo(0, 450);
            this.ctx.strokeStyle = grd;
            this.ctx.lineCap = "round";
            this.ctx.lineWidth = 10;
            this.ctx.stroke();

        },
        drawPatternLine: function () {
            this.clearCanvas();
        },
        clearCanvas: function () {
            this.ctx.beginPath();
            this.ctx.clearRect(0, 0, 800, 400);
        }
    };

    var drawLinCanvas = new DrawLinCanvas(document.getElementById('drawLinCanvas'));

    $('#normalLine').click(function () {
        drawLinCanvas.drawNormalLine();
    });

    //document.getElementById('normalLine').onclick = function () {
    //    drawLinCanvas.drawNormalLine();
    //};

    document.getElementById('diffColorNormalLine').onclick = function () {
        drawLinCanvas.drawDiffColorNormalLine();
    };


    document.getElementById('gradientLine').onclick = function () {
        drawLinCanvas.drawGradientLine();
    };


    document.getElementById('patternLine').onclick = function () {
        drawLinCanvas.drawPatternLine();
    };

    return drawLinCanvas;
});