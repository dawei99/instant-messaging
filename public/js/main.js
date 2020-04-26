document.write('<script src="./js/host.js"></script>');
window.onload = function(){
  var app = new Vue({
      el: '#app',
      data: {
        items: [
        ],
        wsSocket: null,
        sentInput: null,
        username: null,
        showSentInput: false,
        isDisabled:true,
      },
      watch:{
        sentInput : function(){
          if(this.sentInput==null){
              this.isDisabled = true;
          }else{
              this.isDisabled = false;
          }
        }
      },
      mounted: function() {
        this.usernameMake();
        var that = this;
        //Create WebSocket connection.
        that.wsSocket = new WebSocket(WSHOST + '/chat');
        // Connection opened
        that.wsSocket.addEventListener('open', function (event) {
        });

        //Listen for messages
        that.wsSocket.addEventListener('message', function (event) {
          console.log("接受数据：" + event.data);
          console.log(JSON.parse(event.data));
          if(that.showSentInput === true) that.items.push(JSON.parse(event.data));
        });
      },
      methods: {
        send() {
          var data = '{"router": "home.msg","data": "'+this.sentInput+'","ext": {"type": "text"}}';
          console.log("发送消息：" + data);
          this.wsSocket.send(data);
          this.isDisabled = true;
          this.sentInput = null;
        },
        usernameMake: function () {
          var arr = new Array('小明', '小亮', '小红', '小张', '小张');
          this.username = arr[this.randomNum(0, arr.length-1)];
        },
        randomNum: function (minNum, maxNum) {
          switch(arguments.length){
            case 1:
              return parseInt(Math.random()*minNum+1,10);
              break;
            case 2:
              return parseInt(Math.random()*(maxNum-minNum+1)+minNum,10);
              break;
            default:
              return 0;
              break;
          }
        },
        login: function(){
          var data = '{"router": "home.bind","data": "'+this.username+'","ext": {}}';
          console.log("点击进入，发送数据：" + data);
          this.wsSocket.send(data);
          this.showSentInput = true;
        },
        uploadImg: function (e) {
          var that = this;
          var file = e.target.files[0];
          var reader = new FileReader();
          reader.readAsArrayBuffer(file);
          reader.onload = function(e) {
            console.log(e.target.result)

            var data = '{"router": "home.msg","data": "'+e.target.result+'","ext": {"type": "img"}}';
            that.wsSocket.send(data);

            console.log('正在上传数据...');
          }

        }
      }
    })
  }