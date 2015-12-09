/*var canvasBefore = document.getElementById("canvasBefore");
var ctx = c.getContext("2d");
ctx.beginPath();
//centrum od gory, centrum od lewej, promien
ctx.arc(10, 10, 10, 0, 2 * Math.PI);
ctx.fillStyle = "#FF0000";
ctx.fill();

var ctx2 = c.getContext("2d");
ctx2.beginPath();
//od gory, od lewej, wielkość
ctx2.arc(30, 10, 10, 0, 2 * Math.PI);
ctx2.fillStyle = "#FF0000";
ctx2.stroke();*/

var painter = {
    settings: {
        radius: 10,
    },
    raw: {
        url: "../data.json",
        proceed: function (data) {
            painter.raw.resources = data.resources;
            painter.raw.tasks = data.tasks;
            painter.raw.draw();
        },
        canvas: document.getElementById("canvasBefore"),
        resources: [],
        tasks: [],
        draw: function () {
            painter.draw(this.tasks);
            painter.draw(this.resources, "black");
        },
    },
    randomColor: function () {
        var letters = '0123456789ABCDEF'.split('');
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    },
    draw: function (data, color) {
        var previousColor;
        data.forEach(function (e, i) {
            var randomColor = (i % 2 == 0) ? painter.randomColor() : previousColor,
                point = painter.raw.canvas.getContext("2d"),
                radius = e.type ? painter.settings.radius / 1.5 : painter.settings.radius,
                x = radius + e.longitude,
                y = radius + e.latitude;
            point.beginPath();
            point.arc(x, y, radius, 0, 2 * Math.PI);
            point.fillStyle = color ? color : randomColor;
            point.fill();
            if (e.type && e.id % 2 == 1) {
                var line = painter.raw.canvas.getContext("2d");
                line.moveTo(data[i - 1].longitude+radius, data[i - 1].latitude+radius);
                line.lineTo(e.longitude+radius, e.latitude+radius);
                line.strokeStyle = randomColor;
                line.stroke();
            }
            if (!e.type || e.type == "pickup") {
                var pointText = painter.raw.canvas.getContext("2d");
                pointText.font = "10px Arial";
                pointText.fillStyle = !e.type ? 'white' : 'black';
                pointText.fillText(e.task ? e.task : e.id, x - (radius / 2), y + (radius / 3));
            }
            previousColor = randomColor;
            
        })
    },

    get: function (url, callback) {
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
                callback(JSON.parse(xmlHttp.responseText));
        }
        xmlHttp.open("GET", url, true);
        xmlHttp.send(null);
    },
    init: function () {
        this.get(this.raw.url, this.raw.proceed);
    }
};

window.onload = function () {
    painter.init();
}
