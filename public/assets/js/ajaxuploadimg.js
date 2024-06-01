/***
 * ajax 异步上传文件
 */
/**
 使用方法
 <script>

 ajaxuploadimg.init({
            'url':'{:url('ajaxupload')}',//上传路径
            'uploadimgID':'uploadimg',//图片id
            'uploadButtonID':'uploadimg',//触发上传按钮id
            'lodingpath':'__STATIC__/imgs/loding.gif',//加载图
            success:function (res) {
                ajaxuploadimg.setimgsrc('https://tanyue.gitee.io/my-tools/image/pay.jpg');
            }
        });
 ajaxuploadimg.a();//检查是否加载成功可删除
 </script>


 * */

(function (win){
    let ajaxuploadimg={
        config:{
            url:'',//异步上传地址
            findElType:'id',//查找元素方式 可以传id和 el el需要传el对象
            inputfileID:'ajaxupload_inputfile',//inputfilefileid
            uploadimgID:'',
            uploadButtonID:'',
            uploadFiletype:'file',//上传时书写的名字
            issetimg:true,
            lodingpath:'',//加载动画
            success:function (data){
                console.log('返回结果',data)
            }
        },
        init:function (data){
            let that = this;
            let zuheconfig=Object.assign({},that.config,data);
            if(zuheconfig.findElType=='id'){
                var udload_img_el=document.getElementById(zuheconfig.uploadimgID);
                var udload_btn_el=document.getElementById(zuheconfig.uploadButtonID);
            }

            if(zuheconfig.findElType=='el'){
                var udload_img_el=zuheconfig.uploadimgID;
                var udload_btn_el=zuheconfig.uploadButtonID;
            }

            // that.inputfile_el=document.getElementById(that.config.inputfileID);
            udload_img_el.inputfile_el=document.createElement('input');
            udload_img_el.inputfile_el.setAttribute('type','file');
            udload_img_el.inputfile_el.setAttribute('multiple','multiple');
            udload_img_el.inputfile_el.setAttribute('id',that.config.inputfileID);

            //绑定按钮点击事件异步上传
            udload_btn_el.onclick=function(){
                udload_img_el.inputfile_el.click();
            }
            udload_img_el.inputfile_el.onchange=function (file) {
                //that.uplodfile(zuheconfig,udload_img_el.inputfile_el.files[0]);
                for(let s of udload_img_el.inputfile_el.files){
                    that.uplodfile(zuheconfig,s);
                }
                
               //form.append(zuheconfig.uploadFiletype,udload_img_el.inputfile_el.files[0]);
    
            }
            //加载inputfile到页面
            // that.udload_img_el.parentNode.insertBefore(that.inputfile_el,that.udload_img_el);
        },
        uplodfile:function(setconfig,fileitem){
            let that = this;
            let request=new XMLHttpRequest();
            request.open('POST',setconfig.url);
            request.setRequestHeader('X-Requested-With','XMLHttpRequest');
            request.setRequestHeader('Accept','application/json, text/javascript, */*; q=0.01');
            let form=new FormData();
            form.append(setconfig.uploadFiletype,fileitem);
            request.send(form);
            request.onreadystatechange=function () {
                if(request.readyState=='4' && request.status==200){
                    setconfig.success(request.responseText);
                        //json转对象返回
                    var data=JSON.parse(request.responseText)
                    if(setconfig.issetimg==true){
                            that.setimgsrc(udload_img_el,data.data)
                    }
                        
                        //that.config.success(JSON.parse(request.responseText));
                    }
            }
        },
        //修改绑定图片显示src
        setimgsrc:function (el,path){
           el.setAttribute('src',path);
        },
        getconfig:function (){
            console.log(this.config)
        },
    };


    win.ajaxuploadimg=ajaxuploadimg;
})(window);