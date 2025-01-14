import { jsPDF } from "jspdf"
var font = 'undefined';
var callAddFont = function () {
this.addFileToVFS('edwardianscriptitc-normal.ttf', font);
this.addFont('edwardianscriptitc-normal.ttf', 'edwardianscriptitc', 'normal');
};
jsPDF.API.events.push(['addFonts', callAddFont])
