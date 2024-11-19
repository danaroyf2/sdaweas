function revoke(id,type) {
    $.ajax({
        url:location.origin+"/api/Chat/revokemsg",
        type:"post",
        
        beforeSend: function (request) {
           request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        data:{id:id,type:type,token : sessionStorage.getItem("token")},
        dataType:'json',
        success:function (res) {
            if(res.code == 1){
                layer.msg(res.msg,{icon:1,end:function(){
                        $("#xiaox_"+id).remove();
                    }});
            }else{
                layer.msg(res.msg,{icon:2});
            }
        }
    });
}
function getnow(data) {
    $.ajax({
        url:location.origin+"/api/Chat/getchatnow",
        type:"post",
        beforeSend: function (request) {
           request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        data:{sdata:data,token : sessionStorage.getItem("token")},
        dataType:'json',
        success:function (res) {
            console.log("getchatnow",res)
            var a="";
            if(res.code == 0){
               getchat();
            }
        }
    });
}


//储存 频道
var chaarr = new Array();

//初始化 监听
var getonline = function () {
    getchat();
    $.cookie("time","");
    $(".conversation").empty();
};

window.onload = getonline();

// 获取访客状态
function getstatus(cha) {
    $.ajax({
        url:location.origin+'/api/Chat/getstatus',
        beforeSend: function (request) {
           request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        type:'post',
        data:{channel:cha,token : sessionStorage.getItem("token")},
        dataType:'json',
        success:function(res){
            if(res.code ==0){
                if(res.data){
                    $("#last_login_time").text(res.data.timestamp);
                    $("#login_times").text(res.data.login_times);
                    $("#name").val(res.data.name);
                    $(".ipdizhi").text(res.data.ip);
                    $("#tel").val(res.data.tel);
                    $("#comment").val(res.data.comment);
                    if(res.data.extends.os!==undefined){
                        $("#login_device").text(res.data.extends.os + ' ' + res.data.extends.browserName);
                    }
                  if(res.data.state == 'online'){
                    $("#v_state").text("在线");
                  }else{
                    $("#v_state").text("离线");
                  }
                    var data = res.data.area;
                  if(data !== ''){
                      var str = "";
                      str += data[0] + " 、";
                      str += data[1] + " 、";
                      str += data[2];
                      $(".iparea").text(str);
                  }
                }
            }
        }
    });
}

// 正在聊天的队列表
function getchat() {
    $.ajax({
        type:'post',
        url:location.origin+"/api/Chat/getchats",
        
        beforeSend: function (request) {
           request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        data : { token : sessionStorage.getItem("token") },
        success: function (res) {
          //  console.log("getchats",res)
            let url = document.location.toString();
            let visiter_id;
            if(url.indexOf('=') > -1) {
                var arrUrl = url.split("=");
　　　　             visiter_id = arrUrl[1];
                $.ajax({
                    url:location.origin+"/api/Serveruser/openCs",
                   
                    beforeSend: function (request) {
                        request.setRequestHeader("token",sessionStorage.getItem("token"));
                    },
                    type: 'post',
                    data: {
                        visiter_id: visiter_id,
                        token:sessionStorage.getItem("token")
                    },
                    success: function (res) {
                        
                    }
                })
            }
            if (res.code == 1) {
                $('.clear-btn').show();
                $("#chat_list").empty();
                var sdata = sessionStorage.getItem('cu_com');
                if (sdata) {
                    var json = $.parseJSON(sdata);
                    var debug = json.visiter_id;
                } else {
                    var debug = "";
                }
                var data = res.data;
                var chatList = '';
                var paiduiDataStr='';
                var uname;
                let name;
                $.each(data, function (k, v) {
                    var a = '';
                    var str = JSON.stringify(v);
                    chat_data['visiter'+v.vid] =v;
                    v.visiter_name=v.visiter_name?v.visiter_name:'游客'+v.visiter_id;
                    uname=v.name?v.name:v.visiter_name;
                    if((v.name || v.tel) && msgreminder) {
                        name = "<span class='c_name'><span class='c_tag'>已留信息</span><span>" + uname + "</span></span>";
                    }else {
                        name = "<span class='c_name'>" + uname + "</span>";
                    }
                    debug=false;
                    if (debug == v.visiter_id) {

                        $(".chatbox").removeClass('hide');
                        $(".no_chats").addClass('hide');
                       if (v.state == 'online') {
                            a += '<div visid="'+v.visiter_id+'" id="v' + v.channel + '" class="visiter onclick" onmouseover="showcut(this)" onmouseout="hidecut(this)" ><i class="layui-icon myicon hide" title="删除" style="font_weight:blod" onclick="cut(' + "'" + v.visiter_id + "'" + ')">&#x1006;</i><span id="c' + v.channel  + '" class="notice-icon hide"></span>';
                            a += "<div class='visit_content' onclick='choose(" +v.vid+ ")'><img class='am-radius v-avatar' id='img" +v.channel + "' src='" + v.avatar + "' width='50px'>"+name+"<span class='c_time'>" + v.timestamp + "</span><span class='c_status'></span><span class='c_online'>在线</span><div id='msg" +v.channel  + "' class='newmsg'>"+v.content+"</div>";
                            a += '</div></div>';
                        } else {
                            a += '<div visid="'+v.visiter_id+'" id="v' + v.channel + '" class="visiter onclick" onmouseover="showcut(this)" onmouseout="hidecut(this)"><i class="layui-icon myicon hide" title="删除" style="font_weight:blod" onclick="cut(' + "'" + v.visiter_id + "'" + ')">&#x1006;</i><span id="c' +v.channel + '" class="notice-icon hide"></span>';
                            a += "<div class='visit_content' onclick='choose(" +v.vid+ ")'><img class='am-radius v-avatar icon_gray' id='img" + v.channel  + "' src='" + v.avatar + "' width='50px'>"+name+"<span class='c_time'>" + v.timestamp + "</span><span class='c_status_off'></span><span class='c_online_off'>离线</span><div id='msg" +v.channel  + "' class='newmsg'>"+v.content+"</div>";
                            a += '</div></div>';
                        }
                        

                    } else {
                        if(v.count == 0){

                            if (v.state == 'online') {
                                a += '<div visid="'+v.visiter_id+'" id="v' + v.channel + '" class="visiter" onmouseover="showcut(this)" onmouseout="hidecut(this)"><i class="layui-icon myicon hide" title="删除" style="font_weight:blod" onclick="cut(' + "'" + v.visiter_id + "'" + ')">&#x1006;</i><span id="c' +v.channel + '" class="notice-icon hide"></span>';
                                a += "<div class='visit_content' onclick='choose(" +v.vid+ ")'><img class='am-radius v-avatar' id='img" + v.channel + "' src='" + v.avatar + "'  width='50px'>"+name+"<span class='c_time'>" + v.timestamp + "</span><span class='c_status'></span><span class='c_online'>在线</span><div id='msg" + v.channel + "' class='newmsg'>"+v.content+"</div>";
                                a += '</div></div>';
                            } else {
                                a += '<div visid="'+v.visiter_id+'" id="v' + v.channel + '" class="visiter" onmouseover="showcut(this)" onmouseout="hidecut(this)"><i class="layui-icon myicon hide" title="删除" style="font_weight:blod" onclick="cut(' + "'" + v.visiter_id + "'" + ')">&#x1006;</i><span id="c' + v.channel + '" class="notice-icon hide"></span>';
                                a += "<div class='visit_content' onclick='choose(" +v.vid+ ")'><img class='am-radius v-avatar icon_gray' id='img" + v.channel + "' src='" + v.avatar + "'  width='50px'>"+name+"<span class='c_time'>" + v.timestamp + "</span><span class='c_status_off'></span><span class='c_online_off'>离线</span><div id='msg" + v.channel + "' class='newmsg'>"+v.content+"</div>";
                                a += '</div></div>';
                            }

                        }else{
                            if (v.count > 99) {
                                v.count = "99+";
                            }
                            if (v.state == 'online') {
                                a += '<div id="v' + v.channel + '" class="visiter" onmouseover="showcut(this)" onmouseout="hidecut(this)"><i class="layui-icon myicon hide" title="删除" style="font_weight:blod" onclick="cut(' + "'" + v.visiter_id + "'" + ')">&#x1006;</i><span id="c' +v.channel + '" class="notice-icon">'+v.count+'</span>';
                                a += "<div class='visit_content' onclick='choose(" +v.vid+ ")'><img class='am-radius v-avatar' id='img" + v.channel + "' src='" + v.avatar + "'  width='50px'>"+name+"<span class='c_time'>" + v.timestamp + "</span><span class='c_status'></span><span class='c_online'>在线</span><div id='msg" + v.channel + "' class='newmsg'>"+v.content+"</div>";
                                a += '</div></div>';
                            } else {
                                a += '<div  id="v' + v.channel + '" class="visiter" onmouseover="showcut(this)" onmouseout="hidecut(this)"><i class="layui-icon myicon hide" title="删除" style="font_weight:blod" onclick="cut(' + "'" + v.visiter_id + "'" + ')">&#x1006;</i><span id="c' + v.channel + '" class="notice-icon">'+v.count+'</span>';
                                a += "<div class='visit_content' onclick='choose(" +v.vid+ ")'><img class='am-radius v-avatar icon_gray' id='img" + v.channel + "' src='" + v.avatar + "'  width='50px'>"+name+"<span class='c_time'>" + v.timestamp + "</span><span class='c_status_off'></span><span class='c_online_off'>离线</span><div id='msg" + v.channel + "' class='newmsg'>"+v.content+"</div>";
                                a += '</div></div>';
                            }

                        }
                        
                     
                    }
                  chatList+=a;
                  //有未读的的就加入排队中
                  console.log(v.count);
                  if (v.count > 0) {
                      paiduiDataStr+=a;
                  }
                  
                });
                $("#chat_list").append(chatList);
                //console.log(paiduiDataStr);
                $("#wait_list").html(paiduiDataStr);
                
            } else {
                $("#chat_list").empty();
                $(".chatbox").addClass('hide');
                $(".no_chats").removeClass('hide');
               // $.cookie('cu_com', "");
                 sessionStorage.setItem('cu_com',""); 
                $('.clear-btn').hide();
            }
            var count = res.all_unread_count;
            if(count > 0) {
                if(count > 99) {
                    count = '99+'
                }
                if($("#layout-west")[0].offsetWidth == 180) {
                    $(".notices").removeClass('hide')
                }else if($("#layout-west")[0].offsetWidth == 80) {
                    $(".notices-icon").removeClass('hide')
                }
                $(".notices").text(count)
            }else if(count == 0) {
                $(".notices").text('')
                $(".notices").addClass('hide')
                $(".notices-icon").addClass('hide')
            }
        },
        complete:function(){
            choose_lock = false;
        }
    });
}

function showcut(obj){
    $(obj).children('i').removeClass('hide');
}

function hidecut(obj){
    $(obj).children('i').addClass('hide');
}

//获取队列的实时数据
function getwait() {

    $.ajax({
        type:'get',
        url:location.origin+"/api/Chat/getwait",
       
        beforeSend: function (request) {
           request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        data : { token : sessionStorage.getItem("token") },
        dataType:'json',
        success: function (res) {

            if (res.code == 0) {
              
                $("#wait_list").empty();
                $("#waitnum").addClass('hide');
                if (!res.data.length) {
                    return;
                }
                var a = "";
                $.each(res.data, function (k, v) {
                    v.visiter_name=v.visiter_name?v.visiter_name:'游客'+v.visiter_id;
                    var uname=v.name?v.name:v.visiter_name;
                    if(v.state == "online"){
                        a += '<div class="waiter">';
                        a += '<img id="img'+v.visiter_id+'" class="am-radius w-avatar v-avatar" src="' + v.avatar + '" width="50px" height="50px"><span class="wait_name">' + uname + '</span>';
                        a += "<div class='newmsg'>"+v.groupname+"</div>";
                        a += '<i class="mygeticon " title="认领" onclick="get(' + "'" + v.visiter_id + "'" + ')"></i></div>';
                    }else{
                        a += '<div class="waiter">';
                        a += '<img id="img'+v.visiter_id+'"  class="am-radius w-avatar v-avatar icon_gray"  src="' + v.avatar + '" width="50px" height="50px"><span class="wait_name">' + uname + '</span>';
                        a += "<div class='newmsg'>"+v.groupname+"</div>";
                        a += '<i class="mygeticon " title="认领" onclick="get(' + "'" + v.visiter_id + "'" + ')"></i></div>';
                    }
                });
                $("#wait_list").append(a);

                $("#notices-icon").removeClass('hide');
                $("#waitnum").removeClass('hide');
                $("#waitnum").text(res.num);
                document.title ="【有客户等待】"+myTitle;


            } else {

                document.title =myTitle;
            }
        }
    });

}


//获取黑名单
function getblacklist() {


    $.ajax({
        url:location.origin+"/api/Chat/getblackdata",
       
        beforeSend: function (request) {
           request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        data : { token : sessionStorage.getItem("token") },
 
        dataType:'json',
        success: function (res) {

            if (res.code == 0) {
              
                $("#black_list").empty();
                var data = res.data;
                var a = "";
                $.each(data, function (k, v) {

                    a += '<div class="visiter"><img class="am-radius v-avatar" src="' + v.avatar + '">';
                    a += ' <span style="font-size: 14px;color: #555555;line-height: 80px;margin-left: 82px">' + v.visiter_name + '</span><div style="position:absolute;right:0;top:30px;cursor: pointer;" onclick="recovery(' + "'" + v.visiter_id + "'" + ')"><img src="'+YMWL_ROOT_URL+'/assets/images/admin/B/delete.png"></img></div></div>';
                });

                $("#black_list").append(a);
            } else {

                $("#black_list").empty();
            }
        }
    });
}




//获取ip的详细信息
var getip = function (cip) {
    $.ajax({
        url:location.origin+"/api/Serveruser/getipinfo",
        type: "get",
       
        beforeSend: function (request) {
            request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        data: {
            ip: cip,
            token:sessionStorage.getItem("token")
        },
        dataType:'json',
        success: function (res) {

            if(res.code == 1){
                var data = res.data;
                var str = "";
                str += data[0] + " 、";
                str += data[1] + " 、";
                str += data[2];
                $(".iparea").text(str);
                $(".iparea").text(res.data.ip);
            }
           
        }
    })
};

//标记已看消息
function getwatch(cha) {
    $.ajax({
        url:location.origin+"/api/Chat/getwatch",
        
        beforeSend: function (request) {
            request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        type: "post",
        data: {visiter_id: cha,token:sessionStorage.getItem("token")}
    });
}
function isImageUrl(url) {
    var regex = /\.(jpeg|jpg|gif|png)$/i;
    return regex.test(url);
}
function isimg(url){
   
    let regex = /\.(jpeg|jpg|gif|png|bmp|BMP|mpg|MPG|mpeg|MPEG|tis|TIS)$/i;
    return regex.test(url)
}
function isaudio(url){
   
    let regex = /\.(mp3|wav|ogg)$/i;
    return regex.test(url)
}
function isvideo(url){
   
    let regex = /\.(mp4|avi|wmv|MP4|AVI|WMV)$/i;
    return regex.test(url)
}
function updatedata(cha){
    var avatver;
    var sdata =sessionStorage.getItem('cu_com');
    if (sdata) {
        var jsondata = $.parseJSON(sdata);
        avatver = jsondata.avatar;
    }
    var showtime;
    var curentdata =new Date();
    var time =curentdata.toLocaleDateString();
    var cmin =curentdata.getMinutes();
    if($.cookie("hid") != "" ){
        var cid =$.cookie("hid");
    }else{
        var cid ="";
    }
    $.ajax({
        url:location.origin+"/api/Chat/chatdata",
        type: "post",

        beforeSend: function (request) {
            request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        data: {
            visiter_id:cha ,
            hid:"",
            token:sessionStorage.getItem("token")
        },
        dataType:'json',
        success: function (res) {
           

            if (res.code == 0) {
                getwatch(cha);
                var se = $("#chatmsg_submit").attr("name");
                var str = "";
                var data = res.data;
                var user = res.user;
                var mindata = null
                if(res.data.length >0){
                    mindata = data[0].cid;
                } else {
                    mindata = null;
                }
                var pic = $("#se_avatar").attr('src');
                user.visiter_name=user.visiter_name?user.visiter_name:'游客'+user.visiter_id;
                var uname=user.name?user.name:user.visiter_name;
                str += '<div class="chatbox-name"><div class="chatbox-info">';
                str += '<div style="float:left;width:auto;margin-right:5px">'+uname+'</div>';
                str += '<div class="group-list">';
                str += '<div class="group-list-left">';
                for(let i = 0;i < user.group_name_array.length;i++) {
                    str += '<span class="group-item" style="background-color: '+user.bgcolor_array[i]+'">'+user.group_name_array[i]+'</span>'
                }
                str += '</div><div class="group-list-left"><div  alt="" class="editusergroup_gaide" data-vid="'+user.vid+'"></div></div><div class="group-list-right">';
                changetop=user.istop?0:1;
                changetips=user.istop?'取消置顶':'置顶对话';
                btnClass=user.istop?'layui-btn-normal':'layui-btn-danger';

                str +='<button class="layui-btn  chat2top js-ajax-btn"   onclick="chat2top(\''+user.visiter_id+'\',this)" data-istop="'+changetop+'">'+changetips+'</button>';
                str +='</div></div></div></div>';
                $.each(data, function (k, v) {
                    console.log(v);
                    if (v.cid < mindata) {
                        mindata = v.cid;
                    }

                    if(getdata.puttime){
                        if((v.timestamp -getdata.puttime) > 60){
                            var myDate = new Date(v.timestamp*1000);
                            var puttime =myDate.toLocaleDateString();
                            let year = myDate.getFullYear();
                            let month = myDate.getMonth()+1;
                            let date = myDate.getDate();
                            let hours = myDate.getHours();
                            let minutes = myDate.getMinutes();
                            if(hours < 10 ) {
                                minutes = minutes.toString();
                            }
                            if(minutes < 10 ) {
                                minutes = '0'+minutes.toString();
                            }
                            
                            if(puttime == time){
                                showtime =hours+":"+minutes;
                            }else{
                                showtime =year+"-"+month+"-"+date+" "+hours+":"+minutes;
                            }

                        }else{
                            
                            showtime = "";
                        }

                    }else{

                        var myDate = new Date(v.timestamp*1000);
                        var puttime =myDate.toLocaleDateString();
                        if(puttime == time){
                            showtime =myDate.getHours()+":"+myDate.getMinutes();

                        }else{

                            showtime =myDate.getFullYear()+"-"+(myDate.getMonth()+1)+"-"+myDate.getDate()+" "+myDate.getHours()+":"+myDate.getMinutes();
                        }


                    }

                    getdata.puttime = v.timestamp;

                    if(v.content.indexOf('target="_blank') > -1) {
                        v.content = v.content.replace(/alt="">/g,'alt=""></a>')
                    }
                    if (v.direction == 'to_visiter') {
                        str+=creatChatHtmlMdg(v.content);
                        
                        
                    } else{
                        str += '<li class="chatmsg"><div class="showtime">' +showtime+ '</div><div class="" style="position: absolute;left:0;">';
                        str += '<img class="my-circle  se_pic" src="' + v.avatar + '" width="46px" height="46px"></div>';
                        str += "<div class='outer-left'><div class='customer'>";
                       if(isimg(v.content)){
                            str += "<pre><img style='max-width:90px;max-height:90px' src='" + v.content + "'></pre>";
                       }
                       else if(isaudio(v.content)){
                            str += "<audio src='" + v.content + "' controls></audio>";
                       }
                       else if(isvideo(v.content)){
                           str += "<video src='" + v.content + "' controls></video>";
                       }
                        else{
                            str += "<pre>" + v.content + "</pre>";
                        }
                        str += "</div></div>";
                        str += "</li>";
                    }
                });
                
                var div = document.getElementById("wrap");
                if($.cookie("hid") == ""){
                    $(".conversation").html('');
                    $(".conversation").append(str);
                   
                    if(div){          
                        $("img").load(function(){
                            div.scrollTop = div.scrollHeight;
                        });
                    }
                }else{
                    $(".conversation").html('');
                    $(".conversation").append(str);
                    
                    if(res.data.length <= 2){

                        $("#top_div").remove();
                        $(".conversation").append("<div id='top_div' class='showtime'>已没有数据</div>");
                        if(div){
                            div.scrollTop =0;
                        }

                    }else{
                        if(div){
                            div.scrollTop = div.scrollHeight / 3.3;
                        }
                    }
                }
                
                div.scrollTop = div.scrollHeight;
                console.log("最底部")
                $("img[src*='upload/images']").parent().parent('.customer').css({
                    padding: '0',borderRadius: '0'
                });
                $("img[src*='upload/images']").parent().parent('.service').css({
                    padding: '0',borderRadius: '0'
                });
                $("img[src*='data:image/']").parent().parent('.customer').css({
                    padding: '0',borderRadius: '0'
                });
                $("img[src*='data:image/']").parent().parent('.service').css({
                    padding: '0',borderRadius: '0'
                })
                setTimeout(function(){
                    $('.chatmsg').css({
                        height: 'auto'
                    });

                },100)
                if(res.data.length >0){
                    $.cookie("hid",mindata);

                }

            }
        }
    });
  
}

//获取最近历史消息
function getdata(cha) {

    var avatver;
    var sdata =sessionStorage.getItem('cu_com');
    if (sdata) {
        var jsondata = $.parseJSON(sdata);
        avatver = jsondata.avatar;
    }
    var showtime;
    var curentdata =new Date();
    var time =curentdata.toLocaleDateString();
    var cmin =curentdata.getMinutes();
    if($.cookie("hid") != "" ){
        var cid =$.cookie("hid");
    }else{
        var cid ="";
    }
  
    $.ajax({
        url:location.origin+"/api/Chat/chatdata",
        type: "post",
        
        beforeSend: function (request) {
            request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        data: {
            visiter_id: cha,
            hid:cid,
            token:sessionStorage.getItem("token")
        },
        dataType:'json',
        success: function (res) {
            // alert(res);
            if (res.code == 0) {
                getwatch(cha);
                var se = $("#chatmsg_submit").attr("name");
                var str = "";
                var data = res.data;
                var user = res.user;
                var mindata = null
                if(res.data.length >0){
                    mindata = data[0].cid;
                } else {
                    mindata = null;
                }
                var pic = $("#se_avatar").attr('src');
                user.visiter_name=user.visiter_name?user.visiter_name:'游客'+user.visiter_id;
                var uname=user.name?user.name:user.visiter_name;
                str += '<div class="chatbox-name"><div class="chatbox-info">';
                str += '<div style="float:left;width:auto;margin-right:5px">'+uname+'</div>';
                str += '<div class="group-list">';
                str += '<div class="group-list-left">';
                for(let i = 0;i < user.group_name_array.length;i++) {
                    str += '<span class="group-item" style="background-color: '+user.bgcolor_array[i]+'">'+user.group_name_array[i]+'</span>'
                }
                str += '</div><div class="group-list-left"><div  alt="" class="editusergroup_gaide" data-vid="'+user.vid+'"></div></div><div class="group-list-right">';
                changetop=user.istop?0:1;
                changetips=user.istop?'取消置顶':'置顶对话';
                btnClass=user.istop?'layui-btn-normal':'layui-btn-danger';

                str +='<button class="layui-btn  chat2top js-ajax-btn" style="background-color:#076CFE"  onclick="chat2top(\''+user.visiter_id+'\',this)" data-istop="'+changetop+'">'+changetips+'</button>';
                str +='</div></div></div></div>';
                $.each(data, function (k, v) {
                    console.log(v);
                    if (v.cid < mindata) {
                        mindata = v.cid;
                    }

                    if(getdata.puttime){
                        if((v.timestamp -getdata.puttime) > 60){
                            var myDate = new Date(v.timestamp*1000);
                            var puttime =myDate.toLocaleDateString();
                            let year = myDate.getFullYear();
                            let month = myDate.getMonth()+1;
                            let date = myDate.getDate();
                            let hours = myDate.getHours();
                            let minutes = myDate.getMinutes();
                            if(hours < 10 ) {
                                minutes = minutes.toString();
                            }
                            if(minutes < 10 ) {
                                minutes = '0'+minutes.toString();
                            }
                            
                            if(puttime == time){
                                showtime =hours+":"+minutes;
                            }else{
                                showtime =year+"-"+month+"-"+date+" "+hours+":"+minutes;
                            }

                        }else{
                            
                            showtime = "";
                        }

                    }else{

                        var myDate = new Date(v.timestamp*1000);
                        var puttime =myDate.toLocaleDateString();
                        if(puttime == time){
                            showtime =myDate.getHours()+":"+myDate.getMinutes();

                        }else{

                            showtime =myDate.getFullYear()+"-"+(myDate.getMonth()+1)+"-"+myDate.getDate()+" "+myDate.getHours()+":"+myDate.getMinutes();
                        }


                    }

                    getdata.puttime = v.timestamp;

                    if(v.content.indexOf('target="_blank') > -1) {
                        v.content = v.content.replace(/alt="">/g,'alt=""></a>')
                    }
                    if (v.direction == 'to_visiter') {
                        str += '<li class="chatmsg" id="xiaox_'+v.cid+'"><div class="showtime">'+showtime+'</div>';
                        str += '<div class="" style="position: absolute;top: 26px;right: 0;"><img class="my-circle cu_pic" src="' + v.avatar + '" width="46px" height="46px"></div>';
                        str += "<div class='outer-right'><div class='service'>";
                        if(isimg(v.content)){
                            str += "<pre><img src='" +v.content  + "'>&nbsp;&nbsp;<span onclick='revoke("+v.cid+",1);' class='revoke-text'>(撤销)</span></pre>";
                    
                        }
                        else if(isaudio(v.content)){
                            str += "<audio src='" + v.content + "' controls></audio><span onclick='revoke("+v.cid+",1);' class='revoke-text'>(撤销)</span>";
                       }
                       else if(isvideo(v.content)){
                           str += "<video src='" + v.content + "' controls></video><span onclick='revoke("+v.cid+",1);' class='revoke-text'>(撤销)</span>";
                       }
                        else{
                            str += "<pre>" + v.content + "<span onclick='revoke("+v.cid+",1);' class='revoke-text'>(撤销)</span></pre>";
                            
                        }
                        //判断已读和未读
                        if(v.state=='readed'){
                            str += `<div class="readStatus" style="color: green;">已读</div>`;
                        }else{
                            str += `<div class="readStatus" style="color: #999;">未读</div>`;
                        }
                        
                        
                        str += "</div></div>";
                        
                        str += "</li>";
                    } else{
                        str += '<li class="chatmsg"><div class="showtime">' +showtime+ '</div><div class="" style="position: absolute;left:0;">';
                        str += '<img class="my-circle  se_pic" src="' + v.avatar + '" width="46px" height="46px"></div>';
                        str += "<div class='outer-left'><div class='customer'>";
                        str += "<pre>" + v.content + "</pre>";
                        str += "</div></div>";
                        str += "</li>";
                    }
                });

                var div = document.getElementById("wrap");
                if($.cookie("hid") == ""){
                 
                    $(".conversation").append(str);

                    if(div){          
                        $("img").load(function(){
                            div.scrollTop = div.scrollHeight;
                        });
                    }
                }else{
                    $(".conversation").prepend(str);
                    if(res.data.length <= 2){

                        $("#top_div").remove();
                        $(".conversation").prepend("<div id='top_div' class='showtime'>已没有数据</div>");
                        if(div){
                            div.scrollTop =0;
                        }

                    }else{
                        if(div){
                            div.scrollTop = div.scrollHeight / 3.3;
                        }
                    }
                }
                $("img[src*='upload/images']").parent().parent('.customer').css({
                    padding: '0',borderRadius: '0'
                });
                $("img[src*='upload/images']").parent().parent('.service').css({
                    padding: '0',borderRadius: '0'
                });
                $("img[src*='data:image/']").parent().parent('.customer').css({
                    padding: '0',borderRadius: '0'
                });
                $("img[src*='data:image/']").parent().parent('.service').css({
                    padding: '0',borderRadius: '0'
                })
                setTimeout(function(){
                    $('.chatmsg').css({
                        height: 'auto'
                    });

                },100)
                if(res.data.length >0){
                    $.cookie("hid",mindata);

                }

            }
        }
    });
}
$(document).on('click','.editusergroup',function (){
    var vid=$(this).data('vid');
    $.ajax({
        url:location.origin+"/index/ServerUser/user_group_list/vid/"+vid,
        type: "get",
        
        beforeSend: function (request) {
            request.setRequestHeader("token",sessionStorage.getItem("token"));
        },
        data:{
           token:sessionStorage.getItem("token") 
        },
        success: function (res) {
            layer.open({
            skin: 'group',
            type: 1,
            title: '设置分组',
            area: ['300px', 'auto'],
            content: res,
            btn: ['确认', '取消'],
            yes: function (index, layero) {

                let group_id = [];
                var obj = document.getElementsByName("group");
                for (var i = 0; i < obj.length; i++) {
                    if (obj[i].checked)
                        group_id.push(obj[i].value);
                }
                if (group_id.length > 0) {
                    $.ajax({
                        url: location.origin+'/admin/custom/visitergroup',
                        
                        beforeSend: function (request) {
                                request.setRequestHeader("token",sessionStorage.getItem("token"));
                        },
                        type: 'post',
                        data: {
                            group_id: group_id,
                            vid: vid,
                            token:sessionStorage.getItem("token")
                        },
                        dataType: 'json',
                        success: function (res) {
                            if (res.code == 1) {

                                layer.closeAll();
                                layer.msg(res.msg, {icon: 1},function (){
                                    window.location.reload();
                                });
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }
                    });
                }
            }
        });
        }
    })
 

})