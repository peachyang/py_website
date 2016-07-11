//(function () {
//      Section 1 : 按下自定义按钮时执行的代码
//      var a = {
//          exec: function (editor) {
//              alert('upload image!!!');
//          }
//      },
//      b = 'sysimage';
//      CKEDITOR.plugins.add('sysimage', {
//    	 icons:'sysimage',
//         init: function (editor) {
//             editor.addCommand('sysimage', function (editor) {
//                 alert('upload image!!!');
//             });
//             editor.ui.addButton('sysimage', {
//                 label: '管理图片',
//                 icon: this.path + 'sysimage.png',
//                 command: 'sysimage'
//             });
//         };
//         editor.ui.addButton( 'sysimage', {
//             label: 'Insert sysimage',
//             command: 'sysimage',
//             toolbar: 'insert'
//         });
//     });
// })();
