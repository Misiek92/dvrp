var x = document.getElementById("prefix").addEventListener('change', function () {
    refresh(this.value.split(" ")[0]);
});


var getPrefixes = function () {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            var data = xmlHttp.responseText;
            dataArray = data.split("\n");
            for (var i = 0; i < dataArray.length; i++) {
                var x = document.getElementById("prefix");
                var option = document.createElement("option");
                option.text = dataArray[i];
                x.add(option);
            }
        }
    }
    xmlHttp.open("GET", '../algorytmy/results/prefixes.txt', true);
    xmlHttp.send(null);
}

var painter = {
    settings: {
        radius: 8,
    },
    raw: {
        dataId: '',
        url: function () {
            return "../algorytmy/results/" + this.dataId + "-data.json";
        },
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
        var title = painter.raw.canvas.getContext("2d");
        title.font = "20px Arial";
        title.fillStyle = 'black';
        title.fillText("Wygenerowane dane", 10, 30);
        data.forEach(function (e, i) {
            var randomColor = (i % 2 == 0) ? painter.randomColor() : previousColor,
                point = painter.raw.canvas.getContext("2d"),
                radius = painter.settings.radius,
                x = radius + e.longitude,
                y = radius + e.latitude;
            point.beginPath();
            point.arc(x, y, radius, 0, 2 * Math.PI);
            point.fillStyle = color ? color : randomColor;
            point.fill();
            if (e.type == "pickup") {
                var point = painter.raw.canvas.getContext("2d"),
                    radius = 6,
                    x = radius + e.longitude,
                    y = radius + e.latitude;
                point.beginPath();
                point.arc(x + 2, y + 2, radius, 0, 2 * Math.PI);
                point.fillStyle = "white";
                point.fill();
            }
            if (e.type && e.id % 2 == 1) {
                var line = painter.raw.canvas.getContext("2d");
                line.moveTo(data[i - 1].longitude + radius, data[i - 1].latitude + radius);
                line.lineTo(e.longitude + radius, e.latitude + radius);
                line.strokeStyle = randomColor;
                line.stroke();
            }
            var pointText = painter.raw.canvas.getContext("2d");
            pointText.font = "16px Arial";
            pointText.fillStyle = 'black';
            pointText.fillText(typeof (e.task) != "undefined" ? e.task : e.id, x - (radius / 2), y - 13 + (radius / 3));
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
    init: function (dataId) {
        painter.raw.canvas.getContext("2d").clearRect(0, 0, painter.raw.canvas.width, painter.raw.canvas.height);
        this.raw.dataId = dataId;
        this.get(this.raw.url(), this.raw.proceed);
    }
};

var painterNaive = {
    settings: {
        radius: 8,
    },
    raw: {
        dataId: '',
        url: function () {
            return "../algorytmy/results/" + this.dataId + "-naive.json";
        },
        proceed: function (data) {
            painterNaive.raw.paths = data['result'];
            painterNaive.raw.distance = data['totalDistance'];
            painterNaive.raw.time = data['time'];
            painterNaive.raw.theLongest = data['theLongest'];
            painterNaive.raw.draw();
        },
        canvas: document.getElementById("canvasNaive"),
        paths: [],
        distance: 0,
        time: 0,
        theLongest: 0,
        draw: function () {
            painterNaive.draw(this.paths);
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
    draw: function (data) {
        var previousColor;
        var title = painterNaive.raw.canvas.getContext("2d");
        title.font = "20px Arial";
        title.fillStyle = 'black';
        title.fillText("Algorytm naiwny", 10, 30);
        var legend = painterNaive.raw.canvas.getContext("2d");
        legend.font = "16px Arial";
        legend.fillStyle = 'black';
        legend.fillText("Dystans: " + painterNaive.raw.distance, 1600, 890);
        var legend2 = painterNaive.raw.canvas.getContext("2d");
        legend2.font = "16px Arial";
        legend2.fillStyle = 'black';
        legend2.fillText("Czas: " + painterNaive.raw.time, 1622, 910);
        var legend3 = painterNaive.raw.canvas.getContext("2d");
        legend3.font = "16px Arial";
        legend3.fillStyle = 'black';
        legend3.fillText("Najdłuższy: " + painterNaive.raw.theLongest, 1580, 870);
        data.forEach(function (el, j) {
            var randomColor = painterNaive.randomColor();
            el.forEach(function (e, i) {


                var point = painterNaive.raw.canvas.getContext("2d"),
                    radius = painterNaive.settings.radius,
                    x = radius + e.longitude,
                    y = radius + e.latitude;
                point.beginPath();
                point.arc(x, y, radius, 0, 2 * Math.PI);
                point.fillStyle = i == 0 ? "black" : randomColor;
                point.fill();
                if (e.type == "pickup") {
                    var point = painterNaive.raw.canvas.getContext("2d"),
                        radius = 6,
                        x = radius + e.longitude,
                        y = radius + e.latitude;
                    point.beginPath();
                    point.arc(x + 2, y + 2, radius, 0, 2 * Math.PI);
                    point.fillStyle = "white";
                    point.fill();
                }
                if (i > 0) {
                    var line = painterNaive.raw.canvas.getContext("2d");
                    line.moveTo(data[j][i - 1].longitude + radius, data[j][i - 1].latitude + radius);
                    line.lineTo(e.longitude + radius, e.latitude + radius);
                    line.strokeStyle = randomColor;
                    line.stroke();
                }
                var pointText = painterNaive.raw.canvas.getContext("2d");
                pointText.font = "16px Arial";
                pointText.fillStyle = 'black';
                pointText.fillText(typeof (e.task) != "undefined" ? e.task : e.id, x - (radius / 2), y - 13 + (radius / 3));
            });
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
    init: function (dataId) {
        painterNaive.raw.canvas.getContext("2d").clearRect(0, 0, painter.raw.canvas.width, painter.raw.canvas.height);
        this.raw.dataId = dataId;
        this.get(this.raw.url(), this.raw.proceed);
    }
};


var painterOne = {
    settings: {
        radius: 8,
    },
    raw: {
        dataId: '',
        url: function () {
            return "../algorytmy/results/" + this.dataId + "-one.json";
        },
        proceed: function (data) {
            painterOne.raw.paths = data['result'];
            painterOne.raw.distance = data['totalDistance'];
            painterOne.raw.time = data['time'];
            painterOne.raw.theLongest = data['theLongest'];
            painterOne.raw.draw();
        },
        canvas: document.getElementById("canvasOne"),
        paths: [],
        distance: 0,
        time: 0,
        theLongest: 0,
        draw: function () {
            painterOne.draw(this.paths);
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
    draw: function (data) {
        var previousColor;
        var title = painterOne.raw.canvas.getContext("2d");
        title.font = "20px Arial";
        title.fillStyle = 'black';
        title.fillText("#1 Idz do najblizszego", 10, 30);
        var legend = painterOne.raw.canvas.getContext("2d");
        legend.font = "16px Arial";
        legend.fillStyle = 'black';
        legend.fillText("Dystans: " + painterOne.raw.distance, 1600, 890);
        var legend2 = painterOne.raw.canvas.getContext("2d");
        legend2.font = "16px Arial";
        legend2.fillStyle = 'black';
        legend2.fillText("Czas: " + painterOne.raw.time, 1622, 910);
        var legend3 = painterOne.raw.canvas.getContext("2d");
        legend3.font = "16px Arial";
        legend3.fillStyle = 'black';
        legend3.fillText("Najdłuższy: " + painterOne.raw.theLongest, 1580, 870);
        data.forEach(function (el, j) {
            var randomColor = painterOne.randomColor();
            el.forEach(function (e, i) {
                console.log(e);

                var point = painterOne.raw.canvas.getContext("2d"),
                    radius = painterOne.settings.radius,
                    x = radius + e.longitude,
                    y = radius + e.latitude;
                point.beginPath();
                point.arc(x, y, radius, 0, 2 * Math.PI);
                point.fillStyle = i == 0 ? "black" : randomColor;
                point.fill();
                if (e.type == "pickup") {
                    var point = painterOne.raw.canvas.getContext("2d"),
                        radius = 6,
                        x = radius + e.longitude,
                        y = radius + e.latitude;
                    point.beginPath();
                    point.arc(x + 2, y + 2, radius, 0, 2 * Math.PI);
                    point.fillStyle = "white";
                    point.fill();
                }
                if (i > 0) {
                    var line = painterOne.raw.canvas.getContext("2d");
                    line.moveTo(data[j][i - 1].longitude + radius, data[j][i - 1].latitude + radius);
                    line.lineTo(e.longitude + radius, e.latitude + radius);
                    line.strokeStyle = randomColor;
                    line.stroke();
                }
                var pointText = painterOne.raw.canvas.getContext("2d");
                pointText.font = "16px Arial";
                pointText.fillStyle = 'black';
                pointText.fillText(typeof (e.task) != "undefined" ? e.task : e.id, x - (radius / 2), y - 13 + (radius / 3));
            });
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
    init: function (dataId) {
        painterOne.raw.canvas.getContext("2d").clearRect(0, 0, painter.raw.canvas.width, painter.raw.canvas.height);
        this.raw.dataId = dataId;
        this.get(this.raw.url(), this.raw.proceed);
    }
};

var painterTwo = {
    settings: {
        radius: 8,
    },
    raw: {
        dataId: '',
        url: function () {
            return "../algorytmy/results/" + this.dataId + "-two.json";
        },
        proceed: function (data) {
            painterTwo.raw.paths = data['result'];
            painterTwo.raw.distance = data['totalDistance'];
            painterTwo.raw.time = data['time'];
            painterTwo.raw.theLongest = data['theLongest'];
            painterTwo.raw.draw();
        },
        canvas: document.getElementById("canvasTwo"),
        paths: [],
        distance: 0,
        time: 0,
        theLongest: 0,
        draw: function () {
            painterTwo.draw(this.paths);
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
    draw: function (data) {
        var previousColor;
        var title = painterTwo.raw.canvas.getContext("2d");
        title.font = "20px Arial";
        title.fillStyle = 'black';
        title.fillText("#2 Najblizszy na danym etapie", 10, 30);
        var legend = painterTwo.raw.canvas.getContext("2d");
        legend.font = "16px Arial";
        legend.fillStyle = 'black';
        legend.fillText("Dystans: " + painterTwo.raw.distance, 1600, 890);
        var legend2 = painterTwo.raw.canvas.getContext("2d");
        legend2.font = "16px Arial";
        legend2.fillStyle = 'black';
        legend2.fillText("Czas: " + painterTwo.raw.time, 1622, 910);
        var legend3 = painterTwo.raw.canvas.getContext("2d");
        legend3.font = "16px Arial";
        legend3.fillStyle = 'black';
        legend3.fillText("Najdłuższy: " + painterTwo.raw.theLongest, 1580, 870);
        data.forEach(function (el, j) {
            var randomColor = painterTwo.randomColor();
            el.forEach(function (e, i) {
                console.log(e);

                var point = painterTwo.raw.canvas.getContext("2d"),
                    radius = painterTwo.settings.radius,
                    x = radius + e.longitude,
                    y = radius + e.latitude;
                point.beginPath();
                point.arc(x, y, radius, 0, 2 * Math.PI);
                point.fillStyle = i == 0 ? "black" : randomColor;
                point.fill();
                if (e.type == "pickup") {
                    var point = painterTwo.raw.canvas.getContext("2d"),
                        radius = 6,
                        x = radius + e.longitude,
                        y = radius + e.latitude;
                    point.beginPath();
                    point.arc(x + 2, y + 2, radius, 0, 2 * Math.PI);
                    point.fillStyle = "white";
                    point.fill();
                }
                if (i > 0) {
                    var line = painterTwo.raw.canvas.getContext("2d");
                    line.moveTo(data[j][i - 1].longitude + radius, data[j][i - 1].latitude + radius);
                    line.lineTo(e.longitude + radius, e.latitude + radius);
                    line.strokeStyle = randomColor;
                    line.stroke();
                }
                var pointText = painterTwo.raw.canvas.getContext("2d");
                pointText.font = "16px Arial";
                pointText.fillStyle = 'black';
                pointText.fillText(typeof (e.task) != "undefined" ? e.task : e.id, x - (radius / 2), y - 13 + (radius / 3));
            });
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
    init: function (dataId) {
        painterTwo.raw.canvas.getContext("2d").clearRect(0, 0, painter.raw.canvas.width, painter.raw.canvas.height);
        this.raw.dataId = dataId;
        this.get(this.raw.url(), this.raw.proceed);
    }
};

var painterThree = {
    settings: {
        radius: 8,
    },
    raw: {
        dataId: '',
        url: function () {
            return "../algorytmy/results/" + this.dataId + "-three.json";
        },
        proceed: function (data) {
            painterThree.raw.paths = data['result'];
            painterThree.raw.distance = data['totalDistance'];
            painterThree.raw.time = data['time'];
            painterThree.raw.theLongest = data['theLongest'];
            painterThree.raw.draw();
        },
        canvas: document.getElementById("canvasThree"),
        paths: [],
        distance: 0,
        time: 0,
        theLongest: 0,
        draw: function () {
            painterThree.draw(this.paths);
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
    draw: function (data) {
        var previousColor;
        var title = painterThree.raw.canvas.getContext("2d");
        title.font = "20px Arial";
        title.fillStyle = 'black';
        title.fillText("#3 Najmniej wydluzajacy sciezke", 10, 30);
        var legend = painterThree.raw.canvas.getContext("2d");
        legend.font = "16px Arial";
        legend.fillStyle = 'black';
        legend.fillText("Dystans: " + painterThree.raw.distance, 1600, 890);
        var legend2 = painterThree.raw.canvas.getContext("2d");
        legend2.font = "16px Arial";
        legend2.fillStyle = 'black';
        legend2.fillText("Czas: " + painterThree.raw.time, 1622, 910);
        var legend3 = painterThree.raw.canvas.getContext("2d");
        legend3.font = "16px Arial";
        legend3.fillStyle = 'black';
        legend3.fillText("Najdłuższy: " + painterThree.raw.theLongest, 1580, 870);
        data.forEach(function (el, j) {
            var randomColor = painterThree.randomColor();
            el.forEach(function (e, i) {
                console.log(e);

                var point = painterThree.raw.canvas.getContext("2d"),
                    radius = painterThree.settings.radius,
                    x = radius + e.longitude,
                    y = radius + e.latitude;
                point.beginPath();
                point.arc(x, y, radius, 0, 2 * Math.PI);
                point.fillStyle = i == 0 ? "black" : randomColor;
                point.fill();
                if (e.type == "pickup") {
                    var point = painterThree.raw.canvas.getContext("2d"),
                        radius = 6,
                        x = radius + e.longitude,
                        y = radius + e.latitude;
                    point.beginPath();
                    point.arc(x + 2, y + 2, radius, 0, 2 * Math.PI);
                    point.fillStyle = "white";
                    point.fill();
                }
                if (i > 0) {
                    var line = painterThree.raw.canvas.getContext("2d");
                    line.moveTo(data[j][i - 1].longitude + radius, data[j][i - 1].latitude + radius);
                    line.lineTo(e.longitude + radius, e.latitude + radius);
                    line.strokeStyle = randomColor;
                    line.stroke();
                }
                var pointText = painterThree.raw.canvas.getContext("2d");
                pointText.font = "16px Arial";
                pointText.fillStyle = 'black';
                pointText.fillText(typeof (e.task) != "undefined" ? e.task : e.id, x - (radius / 2), y - 13 + (radius / 3));
            });
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
    init: function (dataId) {
        painterThree.raw.canvas.getContext("2d").clearRect(0, 0, painter.raw.canvas.width, painter.raw.canvas.height);
        this.raw.dataId = dataId;
        this.get(this.raw.url(), this.raw.proceed);
    }
};


var painterFinishFirst = {
    settings: {
        radius: 8,
    },
    raw: {
        dataId: '',
        url: function () {
            return "../algorytmy/results/" + this.dataId + "-finishFirst.json";
        },
        proceed: function (data) {
            painterFinishFirst.raw.paths = data['result'];
            painterFinishFirst.raw.distance = data['totalDistance'];
            painterFinishFirst.raw.time = data['time'];
            painterFinishFirst.raw.theLongest = data['theLongest'];
            painterFinishFirst.raw.draw();
        },
        canvas: document.getElementById("canvasFinishFirst"),
        paths: [],
        distance: 0,
        time: 0,
        theLongest: 0,
        draw: function () {
            painterFinishFirst.draw(this.paths);
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
    draw: function (data) {
        var previousColor;
        var title = painterFinishFirst.raw.canvas.getContext("2d");
        title.font = "20px Arial";
        title.fillStyle = 'black';
        title.fillText("#4 Priorytet dla konczenia zadan", 10, 30);
        var legend = painterFinishFirst.raw.canvas.getContext("2d");
        legend.font = "16px Arial";
        legend.fillStyle = 'black';
        legend.fillText("Dystans: " + painterFinishFirst.raw.distance, 1600, 890);
        var legend2 = painterFinishFirst.raw.canvas.getContext("2d");
        legend2.font = "16px Arial";
        legend2.fillStyle = 'black';
        legend2.fillText("Czas: " + painterFinishFirst.raw.time, 1622, 910);
        var legend3 = painterFinishFirst.raw.canvas.getContext("2d");
        legend3.font = "16px Arial";
        legend3.fillStyle = 'black';
        legend3.fillText("Najdłuższy: " + painterFinishFirst.raw.theLongest, 1580, 870);
        data.forEach(function (el, j) {
            var randomColor = painterFinishFirst.randomColor();
            el.forEach(function (e, i) {
                var point = painterFinishFirst.raw.canvas.getContext("2d"),
                    radius = painterFinishFirst.settings.radius,
                    x = radius + e.longitude,
                    y = radius + e.latitude;
                point.beginPath();
                point.arc(x, y, radius, 0, 2 * Math.PI);
                point.fillStyle = i == 0 ? "black" : randomColor;
                point.fill();
                if (e.type == "pickup") {
                    var point = painterFinishFirst.raw.canvas.getContext("2d"),
                        radius = 6,
                        x = radius + e.longitude,
                        y = radius + e.latitude;
                    point.beginPath();
                    point.arc(x + 2, y + 2, radius, 0, 2 * Math.PI);
                    point.fillStyle = "white";
                    point.fill();
                }
                if (i > 0) {
                    var line = painterFinishFirst.raw.canvas.getContext("2d");
                    line.moveTo(data[j][i - 1].longitude + radius, data[j][i - 1].latitude + radius);
                    line.lineTo(e.longitude + radius, e.latitude + radius);
                    line.strokeStyle = randomColor;
                    line.stroke();
                }
                var pointText = painterFinishFirst.raw.canvas.getContext("2d");
                pointText.font = "16px Arial";
                pointText.fillStyle = 'black';
                pointText.fillText(typeof (e.task) != "undefined" ? e.task : e.id, x - (radius / 2), y - 13 + (radius / 3));
            });
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
    init: function (dataId) {
        painterFinishFirst.raw.canvas.getContext("2d").clearRect(0, 0, painter.raw.canvas.width, painter.raw.canvas.height);
        this.raw.dataId = dataId;
        this.get(this.raw.url(), this.raw.proceed);
    }
};

window.onload = function () {
    getPrefixes();
}

function refresh(dataId) {
    painter.init(dataId);
    painterNaive.init(dataId);
    painterOne.init(dataId);
    painterTwo.init(dataId);
    painterThree.init(dataId);
    painterFinishFirst.init(dataId);
}
