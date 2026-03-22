/**
***************************************************************************
* @file mfapi.js
*
* @section LICENSE
*
* Copyright (c) 2003-2016, Insyde Software Corp. All Rights Reserved.
*
* You may not reproduce, distribute, publish, display, perform, modify, adapt,
* transmit, broadcast, present, recite, release, license or otherwise exploit
* any part of this publication in any form, by any means, without the prior
* written permission of Insyde Software Corp.
*
*****************************************************************************/
self.requestFileSystemSync = self.webkitRequestFileSystemSync ||
                             self.requestFileSystemSync;

                             /*
self.addEventListener('message', function(e) {
    var rtn = mfapi(e.data);

    postMessage([rtn]);
}, false);
*/
function mfapi(args) {
    console.log("[mfapi] "+args[0])
    switch (args[0]) {
        case 'CreateImageFromPath':
            return CreateImageFromPath(args[1], args[2]);
        case 'TFATFileSystemImage_VirtualRead':
            return TFATFileSystemImage_VirtualRead(args[1], args[2], args[3], args[4]);
        case 'TFATFileSystemImage_VirtualWrite':
            return TFATFileSystemImage_VirtualWrite(args[1], args[2], args[3], args[4]);
        case 'Folder_RemoveImage':
            return Folder_RemoveImage(args[1]);
        default:
            console.log("mfapi] unknown option:" + args[0]);
            return - 1;
    }
    console.log("--[mfapi] "+args[0]);
}

var boot16_default = [
        0xEB, 0x3E, 0x90, 0x4D, 0x53, 0x57, 0x49, 0x4E, 0x34, 0x2E, 0x31, 0x00, 0x02, 0x40, 0x06, 0x00,
        0x01, 0x00, 0x02, 0x00, 0x00, 0xF8, 0xFA, 0x00, 0x3F, 0x00, 0xFF, 0x00, 0x3F, 0x00, 0x00, 0x00,
        0x00, 0x80, 0x3E, 0x00, 0x80, 0x00, 0x29, 0x00, 0x00, 0x00, 0x00, 0x4E, 0x4F, 0x20, 0x4E, 0x41,
        0x4D, 0x45, 0x20, 0x20, 0x20, 0x20, 0x46, 0x41, 0x54, 0x31, 0x36, 0x20, 0x20, 0x20, 0x33, 0xC9,
        0x8E, 0xD1, 0xBC, 0xF0, 0x7B, 0x8E, 0xD9, 0xB8, 0x00, 0x20, 0x8E, 0xC0, 0xFC, 0xBD, 0x00, 0x7C,
        0x38, 0x4E, 0x24, 0x7D, 0x24, 0x8B, 0xC1, 0x99, 0xE8, 0x3C, 0x01, 0x72, 0x1C, 0x83, 0xEB, 0x3A,
        0x66, 0xA1, 0x1C, 0x7C, 0x26, 0x66, 0x3B, 0x07, 0x26, 0x8A, 0x57, 0xFC, 0x75, 0x06, 0x80, 0xCA,
        0x02, 0x88, 0x56, 0x02, 0x80, 0xC3, 0x10, 0x73, 0xEB, 0x33, 0xC9, 0x8A, 0x46, 0x10, 0x98, 0xF7,
        0x66, 0x16, 0x03, 0x46, 0x1C, 0x13, 0x56, 0x1E, 0x03, 0x46, 0x0E, 0x13, 0xD1, 0x8B, 0x76, 0x11,
        0x60, 0x89, 0x46, 0xFC, 0x89, 0x56, 0xFE, 0xB8, 0x20, 0x00, 0xF7, 0xE6, 0x8B, 0x5E, 0x0B, 0x03,
        0xC3, 0x48, 0xF7, 0xF3, 0x01, 0x46, 0xFC, 0x11, 0x4E, 0xFE, 0x61, 0xBF, 0x00, 0x00, 0xE8, 0xE6,
        0x00, 0x72, 0x39, 0x26, 0x38, 0x2D, 0x74, 0x17, 0x60, 0xB1, 0x0B, 0xBE, 0xA1, 0x7D, 0xF3, 0xA6,
        0x61, 0x74, 0x32, 0x4E, 0x74, 0x09, 0x83, 0xC7, 0x20, 0x3B, 0xFB, 0x72, 0xE6, 0xEB, 0xDC, 0xA0,
        0xFB, 0x7D, 0xB4, 0x7D, 0x8B, 0xF0, 0xAC, 0x98, 0x40, 0x74, 0x0C, 0x48, 0x74, 0x13, 0xB4, 0x0E,
        0xBB, 0x07, 0x00, 0xCD, 0x10, 0xEB, 0xEF, 0xA0, 0xFD, 0x7D, 0xEB, 0xE6, 0xA0, 0xFC, 0x7D, 0xEB,
        0xE1, 0xCD, 0x16, 0xCD, 0x19, 0x26, 0x8B, 0x55, 0x1A, 0x52, 0xB0, 0x01, 0xBB, 0x00, 0x00, 0xE8,
        0x3B, 0x00, 0x72, 0xE8, 0x5B, 0x8A, 0x56, 0x24, 0xBE, 0x0B, 0x7C, 0x8B, 0xFC, 0xC7, 0x46, 0xF0,
        0x3D, 0x7D, 0xC7, 0x46, 0xF4, 0x29, 0x7D, 0x8C, 0xD9, 0x89, 0x4E, 0xF2, 0x89, 0x4E, 0xF6, 0xC6,
        0x06, 0x96, 0x7D, 0xCB, 0xEA, 0x03, 0x00, 0x00, 0x20, 0x0F, 0xB6, 0xC8, 0x66, 0x8B, 0x46, 0xF8,
        0x66, 0x03, 0x46, 0x1C, 0x66, 0x8B, 0xD0, 0x66, 0xC1, 0xEA, 0x10, 0xEB, 0x5E, 0x0F, 0xB6, 0xC8,
        0x4A, 0x4A, 0x8A, 0x46, 0x0D, 0x32, 0xE4, 0xF7, 0xE2, 0x03, 0x46, 0xFC, 0x13, 0x56, 0xFE, 0xEB,
        0x4A, 0x52, 0x50, 0x06, 0x53, 0x6A, 0x01, 0x6A, 0x10, 0x91, 0x8B, 0x46, 0x18, 0x96, 0x92, 0x33,
        0xD2, 0xF7, 0xF6, 0x91, 0xF7, 0xF6, 0x42, 0x87, 0xCA, 0xF7, 0x76, 0x1A, 0x8A, 0xF2, 0x8A, 0xE8,
        0xC0, 0xCC, 0x02, 0x0A, 0xCC, 0xB8, 0x01, 0x02, 0x80, 0x7E, 0x02, 0x0E, 0x75, 0x04, 0xB4, 0x42,
        0x8B, 0xF4, 0x8A, 0x56, 0x24, 0xCD, 0x13, 0x61, 0x61, 0x72, 0x0B, 0x40, 0x75, 0x01, 0x42, 0x03,
        0x5E, 0x0B, 0x49, 0x75, 0x06, 0xF8, 0xC3, 0x41, 0xBB, 0x00, 0x00, 0x60, 0x66, 0x6A, 0x00, 0xEB,
        0xB0, 0x4E, 0x54, 0x4C, 0x44, 0x52, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x0D, 0x0A, 0x52, 0x65,
        0x6D, 0x6F, 0x76, 0x65, 0x20, 0x64, 0x69, 0x73, 0x6B, 0x73, 0x20, 0x6F, 0x72, 0x20, 0x6F, 0x74,
        0x68, 0x65, 0x72, 0x20, 0x6D, 0x65, 0x64, 0x69, 0x61, 0x2E, 0xFF, 0x0D, 0x0A, 0x44, 0x69, 0x73,
        0x6B, 0x20, 0x65, 0x72, 0x72, 0x6F, 0x72, 0xFF, 0x0D, 0x0A, 0x50, 0x72, 0x65, 0x73, 0x73, 0x20,
        0x61, 0x6E, 0x79, 0x20, 0x6B, 0x65, 0x79, 0x20, 0x74, 0x6F, 0x20, 0x72, 0x65, 0x73, 0x74, 0x61,
        0x72, 0x74, 0x0D, 0x0A, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0xAC, 0xCB, 0xD8, 0x55, 0xAA
];

// Constants or enums
var UnitLen = 512;

var DiskCache = 0;
var DiskDirty = 1;

var FAT12 = 0xff8;
var FAT16 = 0xfff8;
var FAT32 = 0xffffff8;
var FATCluBegin = 2;

var FILE_ATTRIBUTE_DIRECTORY = 0x10;

var DirDirectoryAttr = 0x10;
var DirVolumeAttr = 0x8;
var DirHiddenAttr = 0x4;
var DirSystemAttr = 0x2;
var DirReadOnlyAttr = 0x1;

var p_VM_Info = [];

// Global variables in header files
var SecBytes;
var FATCluSec;
var FATSecIdx;
var RootSecIdx;
var DataSecIdx;
var DataSecAll;
var FATCluAll;
var FATType;

var FATOptimized;
var FATCluFreeAll;
var FATCluFreeIdx;

var FATBufSec;
var FATBufPtr;

var ErrFlag;
var FullFlag;
var ErrMsg;

var ErrCode = 0xffffffff;

var LongFileName;
var LongFileCount;
var LFNReady;

var SEPARATOR = "/";
var REPORT_PROCESS = true;

function IdxStruct() {
    this.sec = 0;
    this.idx = 0;
    this.status = 0;
    this.idx1 = -1;
};

// Store all vars needed by mfapi in Fat_Struct
function Fat_Struct() {
    this.fileSystem = null;
    this.ImgFileFolderName = null;
    this.DirPath = null;
    this.f_FolderPathImg = null;
    this.DirVirtual = 0;
    this.m_vIdx = null;
    this.DirPathLen = 0;
    this.IOBuf = new Uint8Array(512 * 128);
    this.DirItemAll = 0;
    this.m_mydir = new My_Dir();
    this.extcount = 0;
    this.CurrentName = null;
    this.SecFileFirst = 0;
    this.DirList = [];
    this.DirItemIdx = 0;
    this.m_nTmpTotal = 0;
    this.LastName = null;
    this.NowName = null;
    this.f_ReadData = 0;
}

function DirMemBuf() {
    // Fields for short name entry
    this.ShortName = null;
    this.Attribute = 0;
    this.ErasedChar = 0;
    this.MSTime = 0;
    this.CreateTime = new Uint32Array(3);
    this.CreateDate = new Uint32Array(3);
    this.AccessDate = new Uint32Array(3);
    this.UpdateTime = new Uint32Array(3);
    this.UpdateDate = new Uint32Array(3);
    this.ClusterFirst = 0;
    this.Size = 0;
    // Fields for making long name entry
    this.LongNum = 0;
    this.Count = 0;
    this.Checksum = 0;
    this.LongName = new Uint8Array(256);
    // Used for operating
    this.ClusterLast = 0;
    this.ClusterAll = 0;
    this.ClusterOptimized = 0;
    this.BufSec = 0;
    this.BufPtr = null;
    // Used for operating if it is a dir itself.
    this.EntryAll = 0;
    this.EntryOptimized = 0;
    this.EntryFreeIdx = 0;
    this.EntryFreeAll = 0;
    this.RootOutFAT = 0;
}

function My_Dir() {
    this.name = null;
    this.type = 0;
    this.item = [];
}

function DirListStruct() {
    this.Index = 0;
    this.Name = 0;
    this.LongFileName = null;
    this.Link = null;
    this.Type = 0;
    this.Size = 0;
    this.First = 0;
    this.Last = 0;
    this.FullName = null;
}

function FileInfo() {
    this.dwFileAttributes = 0;
    this.ftCreationTime = 0;;
    this.ftLastAccessTime = 0;
    this.ftLastWriteTime = 0;
    this.nFileSizeHigh = 0;
    this.nFileSizeLow = 0;
    this.dwReserved0 = 0;
    this.dwReserved1 = 0;
    this.cFileName = null;
    this.cAlternateFileName = null;
    this.lastModified = 0;
};

function jsFile(isFile, path, dirPath, file) {
    this.isFile = isFile;
    this.path = path;
    this.dirPath = dirPath;
    this.file = file;
    this.files = [];
}


function ArrayToString(array, offset, length) {
    var source = array.slice(offset, offset + length);

    var dest = [];
    source.forEach(function callback(currentValue, index, array) {
        dest.push(String.fromCharCode(currentValue));
    });

    return dest.join("");
}

function StringToArray(string) {
    var source = Array.from(string);

    var result = [];
    source.forEach(function callback(currentValue, index, array) {
        result.push(currentValue.charCodeAt());
    });

    return result;
}

function CopyDirMemBuf(dst, src) {
    dst.ShortName = src.ShortName;
    dst.Attribute = src.Attribute;
    dst.ErasedChar = src.ErasedChar;
    dst.MSTime = src.MSTime;
    ArrayCopy(dst.CreateTime, 0, src.CreateTime, 0, 3);
    ArrayCopy(dst.CreateDate, 0, src.CreateDate, 0, 3);
    ArrayCopy(dst.AccessDate, 0, src.AccessDate, 0, 3);
    ArrayCopy(dst.UpdateTime, 0, src.UpdateTime, 0, 3);
    ArrayCopy(dst.UpdateDate, 0, src.UpdateDate, 0, 3);
    dst.ClusterFirst = src.ClusterFirst;
    dst.Size = src.Size;
    dst.LongNum = src.LongNum;
    dst.Count = src.Count;
    dst.Checksum = src.Checksum;
    ArrayCopy(dst.LongName, 0, src.LongName, 0, 256);
    dst.ClusterLast = src.ClusterLast;
    dst.ClusterAll = src.ClusterAll;
    dst.ClusterOptimized = src.ClusterOptimized;
    dst.BufSec = src.BufSec;
    dst.BufPtr = src.BufPtr;
    dst.EntryAll = src.EntryAll;
    dst.EntryOptimized = src.EntryOptimized;
    dst.EntryFreeIdx = src.EntryFreeIdx;
    dst.EntryFreeAll = src.EntryFreeAll;
    dst.RootOutFAT = src.RootOutFAT;
}

function ConvertToDOSFormat(dev_idx, dest, name) {
    var ss;

    var i, lenName, lenExt;
    var chrName;
    var chrExt = [0, 0, 0, 0];

    ss = base(dev_idx, ss, name);

    lenName = ss.length;

    chrName = StringToArray(ss.substring(0, 9));

    for (i = 0; i < lenName; i++) {
        if (lenName > 8 && (i == 6 || i == 7)) {
            chrName[i] = 0;
        } else {
            if (i < chrName.length) {
                chrName[i] = ss[i].charCodeAt();
                if (chrName[i] >= 0x61 && chrName[i] <= 0x7A) {
                    chrName[i] -= 0x20;
                }
            }
        }
    }
    chrName[8] = 0x0;

    if (lenName > 8) {
        chrName[6] = '~'.charCodeAt();
        chrName[7] = '1'.charCodeAt();  
    }

    if (p_VM_Info[dev_idx].extcount != 0) {
        //Get extname
        ss = ext(dev_idx, ss, name);
        lenExt = ss.length;
            
        for (i = 0 ; i < 3 ; i++) {
            if (i < ss.length) {
                chrExt[i] = ss[i].charCodeAt();
            } else {
                chrExt[i] = ' '.charCodeAt();
            }

            if (chrExt[i] >= 0x61 && chrExt[i] <= 0x7A){
                chrExt[i] -= 0x20;
            }
        }   
        chrExt[3] = 0;  
        
        if (lenExt < 3) {
            chrExt[lenExt] = 0; 
        }
    }

    var dosName = [];
    for (i = 0; i < 12; i++) {
        if (i < 8) {
            dosName[i] = String.fromCharCode(chrName[i]);
        } else if (i == 8) {
            dosName[i] = '.';
        } else {
            dosName[i] = String.fromCharCode(chrExt[i - 9]);
        }
    }
    dest.cAlternateFileName = dosName.join("");
}

function ParseFileList(fileList, separator) {
    if (separator == undefined) {
        return null;
    }

    var list = [];
    for (var idx = 0; idx < fileList.length; idx++) {
        var file = fileList[idx];
        list.push(file);
    }

    var rootDir = new jsFile(false, list[0].webkitRelativePath.split(separator)[0], null, null);

    var tree = [[]];
    for (var count = 0; count < list.length; count++) {
        var file = list[count];
        var pathes = file.webkitRelativePath.split(separator);
        var maxDepth = pathes.length - 1;

        var dirs = [pathes[0]];
        for (var depth = 1; depth < maxDepth; depth++) {
            if (depth >= tree.length) {
                tree.push([]);
            }
            var dirPath = dirs.join(separator);
            dirs.push(pathes[depth]);
            var path = dirs.join(separator);
            tree[depth - 1].push(new jsFile(false, path, dirPath, null));
        }

        pathes.pop();
        var dirPath = pathes.join(separator);
        tree[maxDepth - 1].push(new jsFile(true, file.webkitRelativePath, dirPath, file));
    }

    for (var depth = 0; depth < tree.length; depth++) {
        for (var index = 0; index < tree[depth].length; index++) {
            var file = tree[depth][index];
            if (!file.isFile) {
                for (var count = index + 1; count < tree[depth].length; count++) {
                    var checkingFile = tree[depth][count];
                    if (!checkingFile.isFile && (file.path == checkingFile.path)) {
                        tree[depth].splice(count, 1);
                        count--;
                    }
                }
            }
        }
    }

    for (var depth = 0; (tree.length > 0) && (depth < tree.length); depth++) {
        for (var idx = 0; (tree[depth].length > 0) && (idx < tree[depth].length); idx++) {
            var currentFile = tree[depth][idx];
            if (depth > 0) {
                for (var cnt = 0; (tree[depth - 1].length > 0) && (cnt < tree[depth - 1].length); cnt++) {
                    var checkFile = tree[depth - 1][cnt];
                    if (!checkFile.isFile && (checkFile.path == currentFile.dirPath)) {
                        checkFile.files.push(currentFile);
                        //console.log("push " + (currentFile.isFile ? "file " + currentFile.path : "dir " + 
                        //currentFile.path) + " to " + checkFile.path);
                        break;
                    }
                }
            } else {
                //console.log("push "  + (currentFile.isFile ? "file " + currentFile.path : "dir " + currentFile.path) +
                //" to root " + rootDir.path);
                rootDir.files.push(currentFile);
            }
        }
    }

    return rootDir;
}

function CreateImageFromPath(dev_idx, fileList) {
    for (var index = 0; index <= dev_idx; index++) {
        p_VM_Info.push(new Fat_Struct());
    }
console.info(p_VM_Info);
console.info(fileList);

    var dir = new DirMemBuf();
console.info(dir);
    var f_img_name = null;

    p_VM_Info[dev_idx].ImgFileFolderName = [];
    if (fileList instanceof FileList) {
        for (var count = 0; count < fileList.length; count++) {
            p_VM_Info[dev_idx].ImgFileFolderName.push(fileList[count]);
        }
    } else {
console.info("2"+fileList.length);
        p_VM_Info[dev_idx].ImgFileFolderName.push(fileList);
    }
console.info(p_VM_Info[dev_idx].ImgFileFolderName);
    p_VM_Info[dev_idx].DirPath
            = p_VM_Info[dev_idx].ImgFileFolderName[0].webkitRelativePath.split(SEPARATOR)[0];

    f_img_name = "vm" + dev_idx + ".ima";
    p_VM_Info[dev_idx].fileSystem = requestFileSystemSync(PERSISTENT, 3 * 1024 * 1024);
    var dirEntry = p_VM_Info[dev_idx].fileSystem.root.createReader().readEntries();
    for (var idx = 0; idx < dirEntry.length; idx++) { dirEntry[idx].remove(); }
    p_VM_Info[dev_idx].f_FolderPathImg
            = p_VM_Info[dev_idx].fileSystem.root.getFile(f_img_name, { create: true });
console.info(DedicatedWorkerGlobalScope.self);
    InitFATVariable(dev_idx);
    p_VM_Info[dev_idx].DirPathLen = p_VM_Info[dev_idx].DirPath.length;

    p_VM_Info[dev_idx].IOBuf.fill(0);
    ArrayCopy(p_VM_Info[dev_idx].IOBuf, 0, boot16_default, 0, boot16_default.length);
    TFATFileSystem_MakeFAT16BR(2000, p_VM_Info[dev_idx].IOBuf);

    UseUnit(p_VM_Info[dev_idx].f_FolderPathImg, p_VM_Info[dev_idx].IOBuf, 1, 0, 6);

    postMessage(["process", "Open FAT file system..."]);
    TFATFileSystem_Open(dev_idx);

    TFATFileSystem_Format(dev_idx, 1);
    TFATFileSystem_DirMakeRoot(dev_idx, dir);
    p_VM_Info[dev_idx].DirItemAll = 1; //For the root item
    p_VM_Info[dev_idx].m_mydir.name = p_VM_Info[dev_idx].DirPath;
    p_VM_Info[dev_idx].m_mydir.name.toUpperCase();
    p_VM_Info[dev_idx].m_mydir.item = [];
    p_VM_Info[dev_idx].m_mydir.type = 0;
    Linux_TFATFileSystemImage_GetAllFiles(dev_idx, dir, p_VM_Info[dev_idx].m_mydir,
            ParseFileList(p_VM_Info[dev_idx].ImgFileFolderName, SEPARATOR));

    p_VM_Info[dev_idx].SecFileFirst = TFATFileSystem_FATMapSec(FATCluFreeIdx);

    for (var cnt = 0; cnt < p_VM_Info[dev_idx].DirItemAll; cnt++) {
        p_VM_Info[dev_idx].DirList.push(new DirListStruct());
    }
    p_VM_Info[dev_idx].DirItemIdx = 0;
    TFATFileSystemImage_DirItemRecord(dev_idx, "Root", 0, "S".charCodeAt(), 0, 0, 0);
    postMessage(["process", "Expand all files..."]);
    TFATFileSystemImage_ExpandAllFiles(dev_idx, dir, 0);
    p_VM_Info[dev_idx].SecFileLast = TFATFileSystem_FATMapSec(FATCluFreeIdx) - 1;

    if (ErrFlag) {
        return -1;
    }
    p_VM_Info[dev_idx].f_ReadData = 0;
    TFATFileSystemImage_OpenTmpIdxFile(dev_idx);

    postMessage(["process", ""]);
    return 1;
}

function InitFATVariable(dev_idx) {
    ErrFlag = 0;
    p_VM_Info[dev_idx].DirVirtual = 1;
    p_VM_Info[dev_idx].m_vIdx = [];
}

/*
BeforeBPB 11 bytes
    JmpCodes: 0 - 2
    OemName: 3 - 10

BPB 25 bytes
    BytesPerSector: 11 - 12
    SectorsPerCluster: 13
    ReservedSectors: 14 - 15
    NumberOfFATs: 16
    RootEntries: 17 - 18
    TotalSectors: 19 - 20
    Media: 21
    SectorsPerFAT: 22 - 23
    SectorsPerTrack: 24 - 25
    HeadsPerCylinder: 26 - 27
    HiddenSectors: 28 - 31
    TotalSectorsBig: 32 - 35

AfterBPB 27 bytes
    DriverNumber: 36
    Unused: 37
    ExtBootSignature: 38
    SerialNumber: 39 - 42
    VolumeLabel: 43 - 53
    FileSystem: 54 - 61
    BootCodes: 62*/

/**************************************************************************************************
BeforeBPB 11 bytes
    JmpCodes: [0:2]
    OemName: [3:10]

BPB 25 bytes
    BytesPerSector: [0:1]
    SectorsPerCluster: [2]
    ReservedSectors: [3:4]
    NumberOfFATs: [5]
    RootEntries: [6:7]
    TotalSectors: [8:9]
    Media: [10]
    SectorsPerFAT: [11:12]
    SectorsPerTrack: [13:14]
    HeadsPerCylinder: [15:16]
    HiddenSectors: [17:20]
    TotalSectorsBig: [21:24]

BPBExt32 28 bytes
    SectorsPerFAT: [0:3]
    Flags: [4:5]
    Version: [6:7]
    RootCluster: [8:11]
    InfoSector: [12:13]
    BootBackupStart: [14:15]
    Reserved: [16:27]

AfterBPB 27 bytes
    DriverNumber: [0]
    Unused: [1]
    ExtBootSignature: [2]
    SerialNumber: [3:6]
    VolumeLabel: [7:17]
    FileSystem: [18:25]
    BootCodes: [26]
**************************************************************************************************/

function TFATFileSystem_MakeFAT16BR(mb, ptr) {
    var BytesPerSector = 512;
    var ReservedSectors = 6;
    var NumberOfFATs = 1;
    var RootEntries = 512;
    var TotalSectors = mb * 1024 * Math.trunc(1024 / 512);
    var SectorsPerFAT;
    var SectorsPerCluster = 0;
    var tmp;

    if (mb <= 16) {
        SectorsPerCluster = 2;
    } else if (mb <= 128) {
        SectorsPerCluster = 4;
    } else if (mb <= 256) {
        SectorsPerCluster = 8;
    } else if (mb <= 512) {
        SectorsPerCluster = 16;
    } else if (mb <= 1024) {
        SectorsPerCluster = 32;
    } else if (mb <= 2048) {
        SectorsPerCluster = 64;
    } else if (mb <= 4096) {
        SectorsPerCluster=128;
    }

    tmp = Math.trunc(TotalSectors / SectorsPerCluster) * 2;
    SectorsPerFAT = Math.trunc(tmp / BytesPerSector);
    if (tmp % BytesPerSector) {
        SectorsPerFAT++;
    }

    //Before BPB
    var p0 = 0;
    ptr[p0] = "\xEB".charCodeAt();          //JmpCodes [0:2]
    ptr[p0 + 1] = "\x3E".charCodeAt();
    ptr[p0 + 2] = "\x90".charCodeAt();
    ptr[p0 + 3] = "M".charCodeAt();         //OemName [3:10]
    ptr[p0 + 4] = "S".charCodeAt();
    ptr[p0 + 5] = "W".charCodeAt();
    ptr[p0 + 6] = "I".charCodeAt();
    ptr[p0 + 7] = "N".charCodeAt();
    ptr[p0 + 8] = "4".charCodeAt();
    ptr[p0 + 9] = ".".charCodeAt();
    ptr[p0 + 10] = "1".charCodeAt();

    //BPB
    var p1 = 11;
    ptr[p1] = BytesPerSector & 0xff;        //BytesPerSector [0:1]
    ptr[p1 + 1] = BytesPerSector >> 8;
    ptr[p1 + 2] = SectorsPerCluster;        //SectorsPerCluster [2]
    ptr[p1 + 3] = ReservedSectors & 0xff;   //ReservedSectors [3:4]
    ptr[p1 + 4] = ReservedSectors >> 8;
    ptr[p1 + 5] = NumberOfFATs;             //NumberOfFATs [5]
    ptr[p1 + 6] = RootEntries & 0xff;       //RootEntries [6:7]
    ptr[p1 + 7] = RootEntries >> 8;

    if (TotalSectors <= 0xffff) {
        tmp = TotalSectors;
    } else {
        tmp = 0;
    }

    ptr[p1 + 8] = tmp & 0xff;               //TotalSectors [8:9]
    ptr[p1 + 9] = tmp >> 8;
    ptr[p1 + 10] = 0xF8;                    //Media [10]
    ptr[p1 + 11] = SectorsPerFAT & 0xff;    //SectorsPerFAT [11:12]
    ptr[p1 + 12] = SectorsPerFAT >> 8;
    ptr[p1 + 13] = 0x3f;                    //SectorsPerTrack [13:14]
    ptr[p1 + 14] = 0;
    ptr[p1 + 15] = 0xff;                    //HeadsPerCylinder [15:16]
    ptr[p1 + 16] = 0;
    ptr[p1 + 17] = 0x3f;                    //HiddenSectors [17:20]
    ptr[p1 + 18] = 0;
    ptr[p1 + 19] = 0;
    ptr[p1 + 20] = 0;

    if (TotalSectors > 0xffff) {
        tmp = TotalSectors;
    } else {
        tmp = 0;
    }

    ptr[p1 + 21] = tmp & 0xff;              //TotalSectorsBig [21:24]
    ptr[p1 + 22] = (tmp >> 8) & 0xff;
    ptr[p1 + 23] = (tmp >> 16) & 0xff;
    ptr[p1 + 24] = tmp >> 24;

    //After BPB
    var p2 = 36;
    ptr[p2] = 0x80;                         //DriverNumber [0]
    ptr[p2 + 1] = 0x0;                      //Unused [1]
    ptr[p2 + 2] = 0x29;                     //ExtBootSignature [2]
    ptr[p2 + 3] = 0;                        //SerialNumber [3:6]
    ptr[p2 + 4] = 0;
    ptr[p2 + 5] = 0;
    ptr[p2 + 6] = 0;
    var label = [
            "N".charCodeAt(), "O".charCodeAt(),
            " ".charCodeAt(), "N".charCodeAt(),
            "A".charCodeAt(), "M".charCodeAt(),
            "E".charCodeAt(), " ".charCodeAt(),
            " ".charCodeAt(), " ".charCodeAt(),
            " ".charCodeAt()
    ];
    ArrayCopy(ptr, p2 + 7, label, 0, 11);   //VolumeLabel [7:17]
    label = [
            "F".charCodeAt(), "A".charCodeAt(),
            "T".charCodeAt(), "1".charCodeAt(),
            "6".charCodeAt(), " ".charCodeAt(),
            " ".charCodeAt(), " ".charCodeAt()
    ];
    ArrayCopy(ptr, p2 + 18, label, 0, 8);   //FileSystem: [18:25]

    // End of sector
    ptr[510] = 0x55;
    ptr[511] = 0xAA;
}

function UseUnit(fh, WorkBuf, rw, off, num) {
    var addr, len;

    len = UnitLen * num;

    addr = UnitLen * off;

    if (rw == 1) {
        var writer = fh.createWriter();
        if (addr > fh.file().size) {
            writer.truncate(addr);
        }
        writer.seek(addr);
        var blob = new Blob([WorkBuf.slice(0, len)]);
        writer.write(blob);
    } else {
        if ((addr + len) <= fh.file().size) {
            var reader = new FileReaderSync();
            var data = new Uint8Array(reader.readAsArrayBuffer(fh.file().slice(addr, addr + len)));
            ArrayCopy(WorkBuf, 0, data, 0, len);
        }
    }

    return 0;
}

function TFATFileSystem_Open(dev_idx) {
    var SectorsPerFAT, RootEntries, TotalSectors;
    var ptr;
    ErrFlag = 0;
    FullFlag = 0;
    ptr = TFATFileSystem_DiskIO(dev_idx, DiskCache, 0, 1, "Open");
    if (ptr == null) {
        return;
    }

    var p1 = 11; //BPB
    SectorsPerFAT = ptr[p1 + 11] | (p1[p1 + 12] << 8); //SectorsPerFAT: [11:12]

    if (SectorsPerFAT == 0) {
        FATType = FAT32;
        var p2A = 36; //BPBExt32
        SectorsPerFAT = ptr[p2A] | (ptr[p2A + 1] << 8) | (ptr[p2A + 2] << 16) //SectorsPerFAT: [0:3]
                        | (ptr[p2A + 3] << 24);
    } else {
        var p2B = 36; //AfterBPB
        var fat12 = [
                "F".charCodeAt(), "A".charCodeAt(), "T".charCodeAt(), "1".charCodeAt(),
                "2".charCodeAt(), " ".charCodeAt(), " ".charCodeAt(), " ".charCodeAt()
        ];
        if (ArrayCmp(ptr, p2B + 18, fat12, 0, 8)) { //FileSystem: [18:25]
            FATType = FAT12;
        } else {
            FATType = FAT16;
        }
    };

    SecBytes = ptr[p1] | (ptr[p1 + 1] << 8); //BytesPerSector: [0:1]

    FATCluSec = ptr[p1 + 2]; //SectorsPerCluster: [2]

    FATSecIdx = ptr[p1 + 3] | (ptr[p1 + 4] << 8); //ReservedSectors: [3:4]

    RootSecIdx = FATSecIdx + ptr[p1 + 5] * SectorsPerFAT; //NumberOfFATs: [5]

    RootEntries = ptr[p1 + 6] | (ptr[p1 + 7] << 8); //RootEntries: [6:7]

    DataSecIdx = RootSecIdx + Math.trunc(RootEntries * 32 / SecBytes);

    TotalSectors = ptr[p1 + 8] | (ptr[p1 + 9] << 8); //TotalSectors: [8:9]

    if (TotalSectors == 0) {
        TotalSectors = ptr[p1 + 21] | (ptr[p1 + 22] << 8) //TotalSectorsBig: [21:24]
                       | (ptr[p1 + 23] << 16) | (ptr[p1 + 24] << 24);
    }

    DataSecAll = TotalSectors - DataSecIdx;
    FATCluAll = Math.trunc(DataSecAll / FATCluSec);
    TFATFileSystem_FATCluFreePre(dev_idx);
}

function TFATFileSystem_DiskIO(dev_idx, act, idx, num, exp) {
    var buff;

    if (ErrFlag != 0) {
        return null;
    }

    var ptr = VirImgFATFileSysIO(dev_idx, act, idx, num);
    if (ptr != null) {
        return ptr;
    }

    ErrFlag = 1;
    switch (act) {
        case DiskCache:
            buff = "DiskCache";
            break;
        case DiskDirty:
            buff = "DiskDirty";
            break;
        default:
            buff = "Unknow";
            break;
    };
    ErrMsg = "Fail:act=" + buff + ",exp=" + exp + "; Sec:idx=" + idx + ",num=" + num + ".";
    return null;
}

function VirImgFATFileSysIO(dev_idx, type, idx, num) {
    if (type == DiskCache) {
        UseUnit(p_VM_Info[dev_idx].f_FolderPathImg, p_VM_Info[dev_idx].IOBuf, 0, idx, num);
    } else {
        UseUnit(p_VM_Info[dev_idx].f_FolderPathImg, p_VM_Info[dev_idx].IOBuf, 1, idx, num);
    }

    return p_VM_Info[dev_idx].IOBuf;
}

function TFATFileSystem_FATCluFreePre(dev_idx) {
    FATOptimized = 1;
    var freeAll = 0;

    var pcount = 0
    var ptotal = FATCluBegin + FATCluAll;
    for (var idx = FATCluBegin; idx < FATCluBegin + FATCluAll; idx++) {
        if (REPORT_PROCESS && ((idx / ptotal * 100) > pcount)) {
            postMessage(["process", "Open FAT file system..." + pcount + "%"]);
            pcount++;
        }

        var val = TFATFileSystem_FATGetVal(dev_idx, idx);
        if (ErrFlag) {
            return;
        }

        if (val == 0) {
            if (freeAll == 0) {
                FATCluFreeIdx = idx;
            }
            freeAll++;
        } else if (freeAll) {
            FATOptimized = 0;
        }
    };
    if (REPORT_PROCESS) {
        postMessage(["process", "Open FAT file system...100%"]);
    }

    FATCluFreeAll = freeAll;
    if (freeAll == 0) {
        FATCluFreeIdx = FATCluBegin + FATCluAll;
    }
}

function TFATFileSystem_FATGetVal(dev_idx, cluster) {
    var off, pos, val;

    TFATFileSystem_FATGetSec(dev_idx, cluster);

    if (ErrFlag) {
        return ErrCode;
    }

    //FAT buffer starts from FATBufSec
    off = (FATBufSec - FATSecIdx) * SecBytes;
    switch (FATType) {
        case FAT12:
            pos = Math.trunc(cluster / 2) * 3;
            pos -= off;
            if (cluster & 0x1) {
                //odd Cluster(abc): 00 c0 ab
                pos++;
                val = (FATBufPtr[pos] & 0xf0) >> 4;
                val += FATBufPtr[pos + 1] << 4;
            } else {
                //even Cluster(abc):  bc 0a 00
                val = FATBufPtr[pos];
                val += (FATBufPtr[pos + 1] & 0x0f) << 8;
            };
            break;
        case FAT16:
            pos = cluster * 2;  // First byte position of two bytes of a cluster
            pos -= off;
            val = FATBufPtr[pos];
            val += FATBufPtr[pos + 1] << 8;
            break;
        case FAT32:
            pos = cluster * 4;  // First byte position of four bytes of a cluster
            pos -= off;
            val = FATBufPtr[pos];
            val += FATBufPtr[pos + 1] << 8;
            val += FATBufPtr[pos + 2] << 16;
            val += FATBufPtr[pos + 3] << 24;
            break;
        default:
            val = 0xffffffff;
    };

    return val;
}

function TFATFileSystem_FATGetSec(dev_idx, cluster) {
    switch (FATType) {
        case FAT12:
            FATBufSec = Math.trunc(Math.trunc(cluster * 3 / 2) / SecBytes);
            break;
        case FAT16:
            FATBufSec = Math.trunc(cluster * 2 / SecBytes);
            break;
        case FAT32:
            FATBufSec = Math.trunc(cluster * 4 / SecBytes);
            break;
    };

    FATBufSec = FATSecIdx + FATBufSec;
    //************************
    var tmpbuf = new Uint8Array(1024);
    tmpbuf.fill(0);
    var re = TFATFileSystemImage_ReadFromTmp(dev_idx, tmpbuf, 0, FATBufSec);
    if (re) {
        FATBufPtr = tmpbuf;
        TFATFileSystemImage_ReadFromTmp(dev_idx, tmpbuf, 512, FATBufSec + 1);
    } else {
        FATBufPtr = TFATFileSystem_DiskIO(dev_idx, DiskCache, FATBufSec, 2, "FATGetSec");
    }
}

function TFATFileSystemImage_ReadFromTmp(dev_idx, ptr, offset, sec) {
    var idx = TFATFileSystemImage_GetTmpIdx(dev_idx, sec);
    var fs_offset;
    var read_length = 0;

    if (idx == -1) {
        return 0;
    } else {
        fs_offset = p_VM_Info[dev_idx].f_WriteDataTmpFile.size;
        if (fs_offset >= ((idx + 1) * 512)) {
            if (read_length == 0) {
                var reader = new FileReaderSync();
                var data = new Uint8Array(reader.readAsArrayBuffer(
                        p_VM_Info[dev_idx].f_WriteDataTmpFile.slice(idx * 512, 512)));
                ArrayCopy(ptr, offset, data, 0, 512);
            }
            return 1;
        } else {
            return 0;
        }
    }
}

function TFATFileSystemImage_GetTmpIdx(dev_idx, sec) {
    var pos = { value: 0 };
    return TFATFileSystemImage_getInsertPos(dev_idx, sec, pos);
}

function TFATFileSystemImage_getInsertPos(dev_idx, sec, repos) {
    if (p_VM_Info[dev_idx].m_vIdx.length == 0) {
        repos.value = 0;
        return -1;
    }
    repos.value = -1;
    var ret = -1;
    var left = 0;
    var right = p_VM_Info[dev_idx].m_vIdx.length - 1;

    var middle = 0;
    while (left <= right) {
        middle = Math.trunc((right + left) / 2);

        if (p_VM_Info[dev_idx].m_vIdx[middle].sec == sec) {
            repos.value = middle;
            ret = middle;
            break;
        } else {
            if (left == right) {
                if (p_VM_Info[dev_idx].m_vIdx[middle].sec < sec) {
                    repos.value = left + 1;
                } else {
                    repos.value = left;
                }
                ret = -1;
                break;
            }
        }

        if (p_VM_Info[dev_idx].m_vIdx[middle].sec < sec) {
            left = middle + 1;
        } else {
            right = middle - 1;
        }

        if (left > right) {
            repos.value = middle;
            ret = -1;
            break;
        }
    }

    if (ret != -1) {
        return p_VM_Info[dev_idx].m_vIdx[ret].idx;
    } else {
        return ret;
    }
}

function TFATFileSystem_Format(dev_idx, speed) {
    var i, fmt;
    var ptr;

    fmt = DataSecIdx;
    if (speed == 0) {
        fmt += DataSecAll;
    }

    for (i = FATSecIdx; i < fmt; i++) {
        ptr = TFATFileSystem_DiskIO(dev_idx, DiskCache, i, 1, "Format FAT(root)");
        if (ptr == null) {
            return;
        }

        ptr.fill(0, 0, SecBytes);
        if (i == FATSecIdx) {
            switch (FATType) {
                case FAT12:
                    var data = [
                            "\xf0".charCodeAt(), "\xff".charCodeAt(), "\xff".charCodeAt()
                    ];
                    ArrayCopy(ptr, 0, data, 0, 3);
                    break;
                case FAT16:
                    var data = [
                            "\xf8".charCodeAt(), "\xff".charCodeAt(),
                            "\xff".charCodeAt(), "\xff".charCodeAt()
                    ];
                    ArrayCopy(ptr, 0, data, 0, 4);
                    break;
                case FAT32:
                    var data = [
                            "\xff".charCodeAt(), "\xff".charCodeAt(),
                            "\xff".charCodeAt(), "\xff".charCodeAt(),
                            "\xff".charCodeAt(), "\xff".charCodeAt(),
                            "\xff".charCodeAt(), "\xff".charCodeAt()
                    ];
                    ArrayCopy(ptr, 0, data, 0, 8);
                    break;
            };
        };
        TFATFileSystem_DiskIO(dev_idx, DiskDirty, i, 1 , "Format FAT(root)");
    };

    TFATFileSystem_FATCluFreeNew();

    if (FATType == FAT32) {
        ptr = TFATFileSystem_DiskIO(dev_idx, DiskCache, 0, 1, "Format FAT32 Boot");
        if (ptr == null) {
            return;
        }
        var p2A = 36; //BPBExt32
        ptr[p2A + 8] = FATCluBegin; //RootCluster: [8:11]
        ptr[p2A + 9] = 0;
        ptr[p2A + 10] = 0;
        ptr[p2A + 11] = 0;
        TFATFileSystem_DiskIO(dev_idx, DiskDirty, 0, 1, "Format FAT32 Boot");

        for (i = DataSecIdx; i < DataSecIdx + FATCluSec; i++) {
            ptr = TFATFileSystem_DiskIO(dev_idx, DiskCache, i, 1, "Format FAT32 Root");
            if (ptr == null) {
                return;
            }
            ptr.fill(0, 0, SecBytes);
            TFATFileSystem_DiskIO(dev_idx, DiskDirty, i, 1, "Format FAT32 Root");
        };
    };
}

function TFATFileSystem_FATCluFreeNew() {
    FATOptimized = 0;
    FATCluFreeAll = FATCluAll;
    FATCluFreeIdx = FATCluBegin;
}

function TFATFileSystem_DirMakeRoot(dev_idx, dir){
    dir.RootOutFAT = 1;
    TFATFileSystem_DirEntryPre(dev_idx, dir);
    dir.EntryOptimized = 0;
    dir.EntryFreeAll = dir.EntryAll;
    dir.EntryFreeIdx = 0;
}

function TFATFileSystem_DirEntryPre(dev_idx, dir) {
    if (dir.RootOutFAT) {
        dir.EntryAll = Math.trunc((DataSecIdx - RootSecIdx) * SecBytes / 32);
    } else {
        dir.EntryAll = Math.trunc(dir.ClusterAll * FATCluSec * SecBytes / 32);
    }
}

function Linux_TFATFileSystemImage_GetAllFiles(dev_idx, ptr, curdir, jsDir) {
    var fd = new FileInfo();
    var tmp;
    var ss;

    for (var index = 0; index < jsDir.files.length; index++) {
        var jsFile = jsDir.files[index];
        if (!jsFile.isFile) {
            fd.dwFileAttributes |= FILE_ATTRIBUTE_DIRECTORY;
            fd.nFileSizeLow = 0;
        } else {
            fd.nFileSizeLow = jsFile.file.size;
            fd.lastModified = jsFile.file.lastModified;
        }

        //Convert long file name to short file name
        fd.cFileName = (jsFile.isFile) ? jsFile.file.name
                                       : jsFile.path.split(SEPARATOR).pop();
        {
            if (fd.cFileName.length > 12) {
                ss = fd.cFileName;

                ConvertToDOSFormat(dev_idx, fd, ss);
            } else {
                fd.cAlternateFileName = "";//Short file name
            }
        }

        var tdir = new DirMemBuf();

        Main_TFATFileSystemImage_GetOneFile(dev_idx, ptr, tdir, fd, jsFile.path);

        p_VM_Info[dev_idx].CurrentName = jsFile.path;

        var mydir = new My_Dir();
        mydir.name = p_VM_Info[dev_idx].CurrentName;

        if (fd.dwFileAttributes & FILE_ATTRIBUTE_DIRECTORY) {
            tmp = 'd'.charCodeAt();
        } else {
            tmp = 'f'.charCodeAt();
        }

        mydir.type = ((('d'.charCodeAt() - tmp) == 0) ? 0 : 1);
        curdir.item.push(mydir);

        if (tmp == 'd'.charCodeAt()) {
            //My_Dir * curitem;
            var n = curdir.item.length;

            Linux_TFATFileSystemImage_GetAllFiles(dev_idx, tdir, curdir.item[n - 1], jsFile);
        }

        if (FullFlag || ErrFlag) {
            break;
        }
    }
}

function Main_TFATFileSystemImage_GetOneFile(dev_idx, ptr, tptr, pwfd, path) {
    //Win32_TFATFileSystemImage_GetOneFile(dev_idx, ptr, tptr, pwfd, path);
    Linux_TFATFileSystemImage_GetOneFile(dev_idx, ptr, tptr, pwfd, path);
    //Mac_TFATFileSystemImage_GetOneFile(dev_idx,ptr,tptr,(FileInfo *)pwfd,path);
}

function Linux_TFATFileSystemImage_GetOneFile(dev_idx, ptr, tptr, pwfd, path) {
    var dir = ptr;
    var tdir = tptr;
    Main_TFATFileSystem_DirGetFromWin32(dev_idx, tdir, pwfd);
    if (pwfd.dwFileAttributes & FILE_ATTRIBUTE_DIRECTORY) {
       TFATFileSystem_DirMakeSubFrame(dev_idx, dir, tdir);
    }

    TFATFileSystem_DirPutEntity(dev_idx, dir, tdir);
    p_VM_Info[dev_idx].DirItemAll++;
}

function TFATFileSystem_DirMakeSubFrame(dev_idx, fdir, sdir) {
    TFATFileSystem_DirEntryAdd(dev_idx, sdir);
    if (FullFlag) {
        return;
    }
    sdir.EntryOptimized = 0;

    // Add "." entry
    var tdir = new DirMemBuf();
    CopyDirMemBuf(tdir, sdir);
    tdir.ShortName = ".          ";
    tdir.LongNum = 0;
    tdir.ClusterFirst = sdir.ClusterFirst;
    TFATFileSystem_DirPutEntity(dev_idx, sdir, tdir);
    // Add ".." entry
    CopyDirMemBuf(tdir, fdir);
    tdir.ShortName = "..         ";
    tdir.LongNum = 0;
    tdir.ClusterFirst = fdir.ClusterFirst;
    TFATFileSystem_DirPutEntity(dev_idx, sdir, tdir);
}

function TFATFileSystem_DirEntryAdd(dev_idx, dir) {
    if (dir.RootOutFAT) {
        FullFlag = 2;  
        return;
    };

    TFATFileSystem_DirClusterAdd(dev_idx,dir,1);
    if (FullFlag) {
        return;
    }

    var tmp = (dir.ClusterAll - 1) * FATCluSec * 512;
    if (dir.ClusterAll == 0) {
        tmp = 0;
    }
    var sec = TFATFileSystem_DirClusterMap(dev_idx,dir,tmp);

    var ptr = TFATFileSystem_DiskIO(dev_idx, DiskCache, sec, FATCluSec, "DirMakeSub");
    if (ptr != 0) {
        ptr.fill(0, 0, FATCluSec*SecBytes);
    }
    TFATFileSystem_DiskIO(dev_idx, DiskDirty, sec, FATCluSec, "DirMakeSub");

    var num = Math.trunc(FATCluSec * SecBytes / 32);
    dir.EntryAll += num;
    dir.EntryFreeAll += num;
}

function TFATFileSystem_DirPutEntity(dev_idx, dptr, mptr) {
    var i;

    if (dptr.EntryFreeAll < mptr.LongNum + 1) {
        TFATFileSystem_DirEntryAdd(dev_idx, dptr);
        if (FullFlag) {
            return;
        }
    }    

    if (mptr.LongNum) {    
        var strArray = StringToArray(mptr.ShortName);
        mptr.Checksum = TFATFileSystem_MakeDirSum(strArray);
        for (i = 0; i < mptr.LongNum; i++) {
            mptr.Count = mptr.LongNum - i;
            if (i == 0) {
                mptr.Count |= 0x1 << 6;
            }
            TFATFileSystem_DirPutEntry(dev_idx, dptr, dptr.EntryFreeIdx, mptr);
            TFATFileSystem_DirEntryFreeDel(dev_idx, dptr);
            if (ErrFlag) {
                return;
            }
        };
    };
     
    mptr.Count = 0;
    TFATFileSystem_DirPutEntry(dev_idx, dptr, dptr.EntryFreeIdx, mptr);
    TFATFileSystem_DirEntryFreeDel(dev_idx, dptr);
}

function TFATFileSystem_MakeDirSum(ptr) {
    var sum, i;

    sum = 0;
    for (i = 0; i < 11; i++) {
        sum = (((sum & 1) << 7) | ((sum & 0xfe) >> 1)) + ptr[i];
    }

    return (sum & 0xff);
}

function TFATFileSystem_DirEntryFreeDel(dev_idx, dir) {
    dir.EntryFreeAll--;

    if (dir.EntryFreeAll == 0) {
        dir.EntryFreeIdx = dir.EntryAll;
        return;
    };

    if (dir.EntryOptimized) {
        dir.EntryFreeIdx++;
        return;
    };

    for (var idx = dir.EntryFreeIdx + 1; idx < dir.EntryAll; idx++) {
        tdir = new DirMemBuf();
        TFATFileSystem_DirGetEntry(dev_idx, dir, idx, tdir);
        if (ErrFlag) {
            return;
        }
        if ((tdir.ShortName == null) || (tdir.ShortName[0].charCodeAt() == 0) 
            || (tdir.ShortName[0].charCodeAt() == 0xe5)) {
            dir.EntryFreeIdx = idx;
            return;
        };
    };
}

function Main_TFATFileSystem_DirGetFromWin32(dev_idx, dir, pfd) {
    //Win32_TFATFileSystem_DirGetFromWin32(dev_idx,dir,(WIN32_FIND_DATA *)pfd);
    Linux_TFATFileSystem_DirGetFromWin32(dev_idx, dir, pfd);
    //Mac_TFATFileSystem_DirGetFromWin32(dev_idx,dir,(FileInfo *)pfd);
}

function Linux_TFATFileSystem_DirGetFromWin32(dev_idx, dir, pfd) {
    var ss;
    var i;
    var str;

    i = pfd.cFileName.length;
    if (i <= 12) {
        str = pfd.cFileName;
    } else {
        str = pfd.cAlternateFileName;
    }

    if (str != null) {
        dir.LongNum = Main_TFATFileSystem_DirToolStrToLname(
                pfd.cFileName, dir.LongName, pfd.cFileName.length);
    } else {
        str = pfd.cFileName;
    }

    var chrName = null;
    var chrExt = null;

    p_VM_Info[dev_idx].extcount = 0;
    ss = base(dev_idx, ss, str);
    chrName = ss.substring(0, ss.length);

    if (p_VM_Info[dev_idx].extcount != 0) {
        ss = ext(dev_idx, ss, str);
        chrExt = ss.substring(0, ss.length);
    }

    if (chrName.length < 8) {
        dir.ShortName = chrName.substring(0, chrName.length);
        for (var cnt = 0; cnt < 8 - chrName.length; cnt++) {
            dir.ShortName += " ";
        }
    } else {
        dir.ShortName = chrName.substring(0, 8);
    }

    if (p_VM_Info[dev_idx].extcount != 0) {
        if (chrExt.length < 3) {
            dir.ShortName += chrExt;
        } else {
            dir.ShortName += chrExt.substring(0, 3);
        }
    }

    //Convert a string to upper case
    dir.ShortName.toUpperCase();

    var date = new Date(pfd.lastModified);
    dir.CreateDate[0] = 1;
    dir.CreateDate[1] = 1;
    dir.CreateDate[2] = 2009;
    dir.CreateTime[0] = 23;
    dir.CreateTime[1] = 2;
    dir.CreateTime[2] = 15;
    dir.UpdateDate[0] = date.getDate();
    dir.UpdateDate[1] = date.getMonth() + 1;
    dir.UpdateDate[2] = date.getFullYear();
    dir.UpdateTime[0] = Math.trunc(date.getSeconds() / 2);
    dir.UpdateTime[1] = date.getMinutes();
    dir.UpdateTime[2] = date.getHours();
    dir.AccessDate[0] = 9;
    dir.AccessDate[1] = 9;

    if (pfd.dwFileAttributes & FILE_ATTRIBUTE_DIRECTORY) {
        dir.Attribute = dir.Attribute | DirDirectoryAttr;
        return;
    };

    dir.Size = pfd.nFileSizeLow;
}

function Main_TFATFileSystem_DirToolStrToLname(ptr, lptr, len) {
    //return Win32_TFATFileSystem_DirToolStrToLname(ptr, lptr, len);
    return Linux_TFATFileSystem_DirToolStrToLname(ptr, lptr, len);
    //return Mac_TFATFileSystem_DirToolStrToLname(ptr, lptr, len);
}

function Linux_TFATFileSystem_DirToolStrToLname(fileName, lptr, len) {
    var i, num, tmp;
    var j = len;
    var ptr = Array.from(fileName);

    //Convert string to Unicode
    for (i = 0; i < j; i++) {
        lptr[i * 2] = ptr[i].charCodeAt();
        lptr[i * 2 + 1] = 0x0;
    }
    j = j * 2;
    num = Math.trunc(j / 26);
    tmp = j % 26;

    if (tmp == 0) {
        return num;
    } else {
        lptr.fill(0xff, j, j + 26 - tmp);
        lptr.fill(0, j, j + 2);
        return num + 1;
    }
}

function base(dev_idx, ss, name) {
    var b = base_name(name);
    var names = b.split(".");
    var fname;

    if (names.length > 1) {
        fname = b.substring(0, b.length - names[names.length - 1].length - 1);
        p_VM_Info[dev_idx].extcount++;
    } else {
        fname = b;
    }

    /*var bc = 0;
    for (var idx = 0; idx < fname.length; idx++) {
        var ccode = fname[idx].charCodeAt();
        if (ccode <= 0xFF) {
            dest[bc] = ccode;
        } else {
            dest[bc] = ccode & 0xFF;
            bc++;
            dest[bc] = ccode & 0xFF00;
        }

        bc++;
    }*/

    return fname;
}

function base_name(name) {
    var paths = name.split(SEPARATOR);

    return paths[paths.length - 1];
}

function ext(dev_idx, ss, name) {
    var b = base_name(name);
    var names = b.split(".");

    if (names.length > 1) {
        return names[names.length - 1];
    } else {
        return null;
    }
}

function TFATFileSystem_FATMapSec(cluster) {
    return (DataSecIdx + (cluster - FATCluBegin) * FATCluSec);
}

function TFATFileSystemImage_DirItemRecord(dev_idx, str, link, type, size, first, last) {
    p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].Index = p_VM_Info[dev_idx].DirItemIdx;
    p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].Name = str;

    p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].Link = link;
    p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].Type = type;
    p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].Size = size;
    p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].First
            = TFATFileSystem_FATMapSec(first);
    p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].Last
            = TFATFileSystem_FATMapSec(last) + FATCluSec - 1;

    if (type == "D".charCodeAt()) {
        if (LFNReady) {
            LFNReady = 0;

            p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].FullName = LongFileName;
        } else {
            p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].FullName = str;
        }
    } else if (type == "F".charCodeAt()) {
        if (LFNReady) {
            LFNReady = 0;

            p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].FullName = LongFileName;
        } else {
            p_VM_Info[dev_idx].DirList[p_VM_Info[dev_idx].DirItemIdx].FullName = str;
        }
    }

    p_VM_Info[dev_idx].DirItemIdx++;
}

function TFATFileSystemImage_ExpandAllFiles(dev_idx, ptr, link) {
    var dir = ptr;

    TFATFileSystem_DirEntryPre(dev_idx, dir);

    var pos = p_VM_Info[dev_idx].DirPath.length;

    for (var idx = 0; idx < dir.EntryAll; idx++) {
        var tdir = new DirMemBuf();

        TFATFileSystem_DirGetEntry(dev_idx, dir, idx, tdir);
        if (tdir.Count) {
            continue;
        }
        if (tdir.Attribute & DirVolumeAttr) {
            continue;
        }
        if (tdir.ShortName[0] == '.') {
            continue;
        }
        if (tdir.ShortName[0].charCodeAt() == 0) {
            break;
        }

        var str = TFATFileSystem_DirToolSnameToStr(tdir.ShortName);

        p_VM_Info[dev_idx].DirPath += SEPARATOR + str;

        if (tdir.Attribute & DirDirectoryAttr) {
            TFATFileSystem_DirClusterPre(dev_idx, tdir);

            TFATFileSystemImage_DirItemRecord(dev_idx, str, link, "D".charCodeAt(), tdir.Size,
                    tdir.ClusterFirst, tdir.ClusterLast);
            TFATFileSystemImage_ExpandAllFiles(dev_idx, tdir, p_VM_Info[dev_idx].DirItemIdx - 1);
        } else {
            if (p_VM_Info[dev_idx].DirVirtual) {
                TFATFileSystem_DirMakeFileData(dev_idx, tdir, null);
            } else {
                TFATFileSystem_DirMakeFileData(dev_idx, tdir, p_VM_Info[dev_idx].DirPath);
            }
            TFATFileSystem_DirPutEntry(dev_idx, dir, idx, tdir);

            TFATFileSystemImage_DirItemRecord(dev_idx, str, link, 'F'.charCodeAt(), tdir.Size,
                tdir.ClusterFirst, tdir.ClusterLast);
        }

        p_VM_Info[dev_idx].DirPath = p_VM_Info[dev_idx].DirPath.substr(0, pos);

        if (FullFlag || ErrFlag) {
            return;
        }
    };
}

/**************************************************************************************************
DirShortEntry 32 bytes
    unsigned char Name[8];          [0:7]
    unsigned char Ext[3];           [8:10]
    unsigned char Attribute;        [11]
    unsigned char ErasedChar;       [12]
    unsigned char MSTime;           [13]
    unsigned char CreateTime[2];    [14:15]
    unsigned char CreateDate[2];    [16:17]
    unsigned char AccessDate[2];    [18:19]
    unsigned char ClusterH16[2];    [20:21]
    unsigned char UpdateTime[2];    [22:23]
    unsigned char UpdateDate[2];    [24:25]
    unsigned char ClusterL16[2];    [26:27]
    unsigned long Size;             [28:31]

DirLongEntry 32 bytes
    unsigned char Count;            [0]
    unsigned char UniCodes1[10];    [1:10]
    unsigned char Attribute;        [11]
    unsigned char Reserverd1[1];    [12]
    unsigned char Checksum;         [13]
    unsigned char UniCodes2[12];    [14:25]
    unsigned char Reserverd2[2];    [26:27]
    unsigned char UniCodes3[4];     [28:31]
**************************************************************************************************/

function TFATFileSystem_DirGetEntry(dev_idx, dptr, idx, mptr) {
    var off, tmp;

    tmp = idx * 32;
    dptr.BufSec = TFATFileSystem_DirClusterMap(dev_idx, dptr, tmp);

    //************************
    var tmpbuf = new Uint8Array(512);
    tmpbuf.fill(0);
    var re = TFATFileSystemImage_ReadFromTmp(dev_idx, tmpbuf, dptr.BufSec);

    if (re) {
        dptr.BufPtr = tmpbuf;
    } else {
        dptr.BufPtr = TFATFileSystem_DiskIO(dev_idx, DiskCache, dptr.BufSec, 1, "DirGetSec");
    }
    if (ErrFlag) {
        return;
    }
    off = tmp % SecBytes;

    if (dptr.BufPtr != null) {
        if (dptr.BufPtr[off] == 0xE5) {
            return;
        }
    }

    var sptr = off;
    var lptr = off;

    var ptr = dptr.BufPtr;
    if (ptr[sptr + 11] != 0xf) { // Attribute [11]
        // Short name entry
        mptr.ShortName = ArrayToString(ptr, sptr, 11); // Name [0:7]
        mptr.Attribute = ptr[sptr + 11]; // Attribute [11]
        mptr.ErasedChar = ptr[sptr + 12]; // ErasedChar [12]
        mptr.MSTime = ptr[sptr + 13]; // MSTime [13]
        TFATFileSystem_PackDirTime(0, mptr.CreateTime, ptr, sptr + 14); // CreateTime [14:15]
        TFATFileSystem_PackDirDate(0, mptr.CreateDate, ptr. sptr + 16); // CreateDate [16:17]
        TFATFileSystem_PackDirDate(0, mptr.AccessDate, ptr, sptr + 18); // AccessDate [18:19]
        TFATFileSystem_PackDirTime(0, mptr.UpdateTime, ptr, sptr + 22); // UpdateTime [22:23]
        TFATFileSystem_PackDirDate(0, mptr.UpdateDate, ptr, sptr + 24); // UpdateDate [24:25]
        mptr.ClusterFirst = ptr[sptr + 26] | (ptr[sptr + 27] << 8)      // ClusterL16 [26:27]
                | (ptr[sptr + 20] << 16) | (ptr[sptr + 21] << 24);      // ClusterH16 [20:21]
        mptr.Size = ptr[sptr + 28] | (ptr[sptr + 29] << 8)              // Size [28:31]
                | (ptr[sptr + 30] << 16) | (ptr[sptr + 31] << 24);
    } else {
        // Long name entry
        mptr.Count = ptr[lptr]; // Count [0]
        mptr.Checksum = ptr[lptr + 13]; // Checksum [13]
        tmp = ((mptr.Count & 0x3f) - 1) * 26;
        ArrayCopy(mptr.LongName, tmp, ptr, lptr + 1, 10); // UniCodes1 [1:10]
        ArrayCopy(mptr.LongName, tmp + 10, ptr, lptr + 14, 10); // UniCodes2 [14:25]
        ArrayCopy(mptr.LongName, tmp + 22, ptr, lptr + 28, 4); // UniCodes3 [28:31]

        // Following codes are for Linux only START ////////////////////////////////////////////////
        if ((mptr.Count & 0x40) == 0x40) {
               LFNReady = 0;

            LongFileCount = ((mptr.Count & 0x3f) - 1) * 13;

            var lfn = [];
            if ((ptr[lptr + 1] != 0x00) && (ptr[lptr + 1] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 1];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 1 + 2] != 0x00) && (ptr[lptr + 1 + 2] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 1 + 2];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 1 + 4] != 0x00) && (ptr[lptr + 1 + 4] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 1 + 4];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 1 + 6] != 0x00) && (ptr[lptr + 1 + 6] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 1 + 6];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 1 + 8] != 0x00) && (ptr[lptr + 1 + 8] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 1 + 8];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 14] != 0x00) && (ptr[lptr + 14] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 14];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 14 + 2] != 0x00) && (ptr[lptr + 14 + 2] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 14 + 2];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 14 + 4] != 0x00) && (ptr[lptr + 14 + 4] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 14 + 4];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 14 + 6] != 0x00) && (ptr[lptr + 14 + 6] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 14 + 6];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 14 + 8] != 0x00) && (ptr[lptr + 14 + 8] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 14 + 8];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 14 + 10] != 0x00) && (ptr[lptr + 14 + 10] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 14 + 10];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 28] != 0x00) && (ptr[lptr + 28] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 28];
                }
                LongFileCount++;
            }

            if ((ptr[lptr + 28 + 2] != 0x00) && (ptr[lptr + 28 + 2] != 0xff)) {
                if (LongFileCount >= 0) {
                    lfn[LongFileCount] = ptr[lptr + 28 + 2];
                }
                LongFileCount++;
            }

            LongFileName = ArrayToString(lfn, 0, lfn.length);

            if ((mptr.Count == 0x41) && (LongFileCount <= 13)) {
                    LFNReady = 1;
            }
        } else {
            if ((((mptr.Count & 0x3f) -1 ) * 13) >= 0) {
                var lfn = [];
                lfn[((mptr.Count & 0x3f) - 1) * 13] = ptr[lptr + 1];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 1] = ptr[lptr + 1 + 2];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 2] = ptr[lptr + 1 + 4];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 3] = ptr[lptr + 1 + 6];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 4] = ptr[lptr + 1 + 8];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 5] = ptr[lptr + 14];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 6] = ptr[lptr + 14 + 2];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 7] = ptr[lptr + 14 + 4];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 8] = ptr[lptr + 14 + 6];;
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 9] = ptr[lptr + 14 + 8];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 10] = ptr[lptr + 14 + 10];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 11] = ptr[lptr + 28];
                lfn[(((mptr.Count & 0x3f) -1) * 13) + 12] = ptr[lptr + 28 + 2];
                LongFileName = ArrayToString(lfn, 0, lfn.length);
            }

            if ((mptr.Count & 0x3f) == 1) {
                LFNReady = 1;
            }
        }
        // Following codes are for Linux only END   ////////////////////////////////////////////////
    }
}

function TFATFileSystem_DirClusterMap(dev_idx, dir, off) {
    var sec, clusterIdx, clusterOff, cluster, i;

    // Only for root dir of FAT12/16
    sec = Math.trunc(off / SecBytes);

    if (dir.RootOutFAT) {
        return RootSecIdx + sec;
    }
    if (dir.ClusterFirst == 0) {
        return ErrCode;
    }

    // All other situations
    clusterIdx = Math.trunc(sec / FATCluSec);
    clusterOff = sec % FATCluSec;
    cluster = dir.ClusterFirst;

    if (dir.ClusterOptimized) {
        cluster += clusterIdx;
    } else {
        for (i = 0; i < clusterIdx; i++) {
            cluster = TFATFileSystem_FATGetVal(dev_idx, cluster);
        }
    }

    sec = TFATFileSystem_FATMapSec(cluster) + clusterOff;
    return sec;
}

function TFATFileSystem_PackDirTime(type, lptr, bptr, boffset) {
    var tmp;

    if (type) {
        tmp = lptr[0];
        tmp |= lptr[1] << 5;
        tmp |= lptr[2] << 11;
        bptr[boffset] = tmp & 0xff;
        bptr[boffset + 1] = tmp >> 8;
    } else {
        tmp = bptr[boffset];
        tmp = tmp | bptr[boffset + 1] << 8;
        lptr[0] = tmp & 0x1f;
        lptr[1] = (tmp >> 5) & 0x3f;
        lptr[2] = tmp >> 11;
    };
}

function TFATFileSystem_PackDirDate(type, lptr, bptr, boffset) {
    var tmp;

    if (type) {
        tmp = lptr[0];
        tmp |= lptr[1] << 5;
        tmp |= (lptr[2] - 1980) << 9;
        bptr[boffset] = tmp & 0xff;
        bptr[boffset + 1] = tmp >> 8;
    } else {
        tmp = bptr[boffset];
        tmp = tmp | (bptr[boffset + 1] << 8);
        lptr[0] = tmp & 0x1f;
        lptr[1] = (tmp >> 5) & 0xf;
        lptr[2] = (tmp >> 9) + 1980;
    };
}

function TFATFileSystem_DirToolSnameToStr(sptr) {
    var si, di, dot;
    var chr;
    var source = Array.from(sptr);
    var ptr = [];

    di = 0;
    for (si = 0; si < 8; si++) {
        chr= source[si].charCodeAt();
        if ((chr == " ".charCodeAt()) || (chr == 0)) {
            break;
        }
        ptr[di] = chr;
        di++;
    };

    dot = 0;
    for (si = 8; (si < source.length) && (si < 11); si++) {
        chr = source[si].charCodeAt();
        if ((chr == " ".charCodeAt()) || (chr == 0)) {
            break;
        }
        if (dot == 0) {
            dot = 1;
            ptr[di] = ".".charCodeAt();
            di++;
        };
        ptr[di] = chr;
        di++;
    };

    return ArrayToString(ptr, 0, ptr.length);
}

function TFATFileSystem_DirClusterPre(dev_idx, dir) {
    if (dir.RootOutFAT) {
        return;
    }
    if (dir.ClusterFirst == 0) {
        return;
    }

    var cluAll = 1;
    var cluIdx = dir.ClusterFirst;
    dir.ClusterOptimized = 1;
    while (1) {
        var cluVal = TFATFileSystem_FATGetVal(dev_idx, cluIdx);
        if (cluVal >= FATType) {
            break;
        }
        if (cluVal != (cluIdx + 1)) {
            dir.ClusterOptimized = 0;
        }
        cluIdx = cluVal;
        cluAll++;
    };
    dir.ClusterLast = cluIdx;
    dir.ClusterAll = cluAll;
}

function TFATFileSystem_DirMakeFileData(dev_idx, dir, fname) {
    var secAll = Math.trunc(dir.Size / SecBytes);
    if (dir.Size % SecBytes) {
        secAll++;
    }
    var cluAll = Math.trunc(secAll / FATCluSec);
    if (secAll % FATCluSec) {
        cluAll++;
    }

    TFATFileSystem_DirClusterAdd(dev_idx, dir, cluAll);
    if (FullFlag) {
        return;
    }

    var file = GetFileFromName(dev_idx, fname);
    if (file == null) {
        return;
    }

    var num = FATCluSec * SecBytes;
    for (var i = 0; i < cluAll; i++) {
        var sec = TFATFileSystem_DirClusterMap(dev_idx, dir, i * num);
        var ptr = TFATFileSystem_DiskIO(dev_idx, DiskCache, sec, FATCluSec, "DataGetFromClib");
        if (ptr == null) {
            break;
        }
        if (i == (cluAll - 1)) {
            num = dir.Size - i * num;
        }

        var reader = new FileReaderSync();
        var data = new Uint8Array(reader.readAsArrayBuffer(file.slice(0, num)));
        if (data == null) {
            break;
        }
        ArrayCopy(ptr, 0, data, 0, num);
        TFATFileSystem_DiskIO(dev_idx, DiskDirty, sec, FATCluSec, "DataGetFromClib");
    };
}

function TFATFileSystem_DirClusterAdd(dev_idx, dir, num) {
    if (FATCluFreeAll < num) {
        FullFlag = 1;
        return;
    };

    if (dir.ClusterAll == 0) {
        dir.ClusterOptimized = 1;
        dir.ClusterFirst = FATCluFreeIdx;
    };

    var fname = TFATFileSystem_DirToolSnameToStr(dir.ShortName);
    var pcount = 0;
    for (var i = 0; i < num; i++) {
        if (REPORT_PROCESS && !(dir.Attribute & DirVolumeAttr) && ((i / num * 100) > pcount)) {
            postMessage(["process", "Porcess " + fname + "..." + pcount + "%"]);
            pcount++;
        }

        if (dir.ClusterAll) {
            TFATFileSystem_FATPutVal(dev_idx, dir.ClusterLast, FATCluFreeIdx);
            if (ErrFlag) {
                return;
            }
            if (FATCluFreeIdx != (dir.ClusterLast + 1)) {
                dir.ClusterOptimized = 0;
            }
        };
        dir.ClusterAll++;
        dir.ClusterLast = FATCluFreeIdx;
        TFATFileSystem_FATCluFreeDel(dev_idx);
    }
    if (REPORT_PROCESS && !(dir.Attribute & DirVolumeAttr)) {
        postMessage(["process", "Porcess " + fname + "...100%"]);
        pcount++;
    }

    TFATFileSystem_FATPutVal(dev_idx,dir.ClusterLast,FATType);
}

function TFATFileSystem_FATPutVal(dev_idx, cluster, value) {
    var off, pos;

    TFATFileSystem_FATGetSec(dev_idx, cluster);
    if (ErrFlag) {
        return;
    }

    //FAT buffer starts from FATBufSec
    off = (FATBufSec - FATSecIdx) * SecBytes;
    switch (FATType) {
        case FAT12:
            pos = Math.trunc(cluster / 2) * 3;
            pos -= off;
            if (cluster & 0x1) {
                //odd Cluster abc: 00 c0 ab
                pos++;
                FATBufPtr[pos] &= 0x0f;
                FATBufPtr[pos] |= (value & 0x00f) << 4;
                FATBufPtr[pos + 1] = (value & 0xff0) >> 4;
            } else {
                //even Cluster abc:  bc 0a 00
                FATBufPtr[pos] = value & 0x0ff;
                FATBufPtr[pos + 1] &= 0xf0;
                FATBufPtr[pos + 1] |= (value & 0xf00) >> 8;
            };
            break;
        case FAT16:
            pos = cluster * 2;  // First byte position of two bytes of a cluster
            pos -= off;
            FATBufPtr[pos] = value & 0x00ff;
            FATBufPtr[pos + 1] = (value & 0xff00) >> 8;
            break;
        case FAT32:
            pos = cluster * 4;  // First byte position of four bytes of a cluster
            pos -= off;
            FATBufPtr[pos] = value & 0x000000ff;
            FATBufPtr[pos + 1] = (value & 0x0000ff00) >> 8;
            FATBufPtr[pos + 2] = (value & 0x00ff0000) >> 16;
            FATBufPtr[pos + 3] = (value & 0xff000000) >> 24;
            break;
    };

    TFATFileSystem_DiskIO(dev_idx, DiskDirty, FATBufSec, 2, "FATPutVal");
}

function TFATFileSystem_FATCluFreeDel(dev_idx) {
    FATCluFreeAll--;
    if (FATCluFreeAll == 0) {
        FATCluFreeIdx = FATCluBegin + FATCluAll;
        return;
    };

    if (FATOptimized) {
        FATCluFreeIdx++;
        return;
    };

    for (var idx = FATCluFreeIdx + 1; idx < FATCluBegin + FATCluAll; idx++) {
        var val = TFATFileSystem_FATGetVal(dev_idx, idx);
        if (ErrFlag) {
            return;
        }
        if (val == 0) {
            FATCluFreeIdx = idx;
            return;
        };
    };
}

function GetFileFromName(dev_idx, name) {
    for (var idx = 0; idx < p_VM_Info[dev_idx].ImgFileFolderName.length; idx++) {
        var file = p_VM_Info[dev_idx].ImgFileFolderName[idx];
        if (file.webkitRelativePath == name) {
            return file;
        }
    }

    return null;
}

function TFATFileSystem_DirPutEntry(dev_idx, dptr, idx, mptr) {
    var off = 0;
    var tmp = 0;

    off = idx * 32;

    dptr.BufSec = TFATFileSystem_DirClusterMap(dev_idx, dptr, off);
    dptr.BufPtr = TFATFileSystem_DiskIO(dev_idx, DiskCache, dptr.BufSec, 1, "DirPutEntry");

    if (ErrFlag) {
        return;
    }
    off = off % SecBytes;

    var ptr = dptr.BufPtr;
    var sptr = off;
    var lptr = off;

    if (mptr.Count == 0) {
        // Short name entry
        var data = StringToArray(mptr.ShortName);
        ArrayCopy(ptr, sptr, data, 0, 11);
        ptr[sptr + 11] = mptr.Attribute;

        ptr[sptr + 12] = mptr.ErasedChar;
        ptr[sptr + 13] = mptr.MSTime;

        TFATFileSystem_PackDirTime(1, mptr.CreateTime, ptr, sptr + 14);
        TFATFileSystem_PackDirDate(1, mptr.CreateDate, ptr, sptr + 16);
        TFATFileSystem_PackDirDate(1, mptr.AccessDate, ptr, sptr + 18);
        TFATFileSystem_PackDirTime(1, mptr.UpdateTime, ptr, sptr + 22);
        TFATFileSystem_PackDirDate(1, mptr.UpdateDate, ptr, sptr + 24);

        {
            ptr[sptr + 26] = mptr.ClusterFirst & 0xff;
            ptr[sptr + 27] = (mptr.ClusterFirst >> 8) & 0xff;
            ptr[sptr + 20] = (mptr.ClusterFirst >> 16) & 0xff;
            ptr[sptr + 21] = (mptr.ClusterFirst >> 24) & 0xff;

            ptr[sptr + 28] = mptr.Size & 0xFF;
            ptr[sptr + 29] = (mptr.Size >> 8) & 0xFF;
            ptr[sptr + 30] = (mptr.Size >> 16) & 0xFF;
            ptr[sptr + 31] = (mptr.Size >> 24) & 0xFF;
        }
    } else {
        // Long name entry
        {
            ptr[lptr] = mptr.Count;
            ptr[lptr + 11] = 0xf;
            ptr[lptr + 13] = mptr.Checksum;
        }
        tmp = ((mptr.Count & 0x3f) - 1) * 26;
        if ((tmp < 230) && (tmp >= 0)) {
            ArrayCopy(ptr, lptr + 1, mptr.LongName, tmp, 10);
            ArrayCopy(ptr, lptr + 14, mptr.LongName, tmp + 10, 12);
            ArrayCopy(ptr, lptr + 28, mptr.LongName, tmp + 22, 4);
        }
        ptr[lptr + 12] = 0;
        ptr[lptr + 26] = 0;
        ptr[lptr + 27] = 0;
    };

    TFATFileSystem_DiskIO(dev_idx, DiskDirty, dptr.BufSec, 1, "DirPutEntry");
}

function TFATFileSystemImage_OpenTmpIdxFile(dev_idx) {
    p_VM_Info[dev_idx].WriteDataTmpFileFullPath = "Img" + dev_idx + ".dat";

    p_VM_Info[dev_idx].f_WriteDataTmpFile = p_VM_Info[dev_idx].fileSystem.root.getFile(
            p_VM_Info[dev_idx].WriteDataTmpFileFullPath, { create: true });
    p_VM_Info[dev_idx].m_nTmpTotal = 0;
    return 1;
}

function TFATFileSystemImage_InsertTmpIdx(dev_idx, idx, sec, pos){
    var ids = new IdxStruct();
    ids.idx = idx;
    ids.sec = sec;
    ids.status = 0;
    p_VM_Info[dev_idx].m_vIdx.splice(pos, 0, ids);
    return 0;
}


function TFATFileSystemImage_VirtualRead(dev_idx, Unused, offset, sec)
{
    var dptr; //DirList index
    var i,len;
    var OSName;
    var WorkBuf = new Uint8Array(512);

    if(TFATFileSystemImage_ReadFromTmp(dev_idx, WorkBuf, sec)) {
        return [WorkBuf, offset];
    }

    if (sec < p_VM_Info[dev_idx].SecFileFirst) {
        UseUnit(p_VM_Info[dev_idx].f_FolderPathImg, WorkBuf, 0, sec, 1);
        return [WorkBuf, offset];
    }

    if (sec > p_VM_Info[dev_idx].SecFileLast){
        WorkBuf.fill(0, UnitLen);
        return [WorkBuf, offset];
    }

    // Get file name
    dptr = 1;
    for (i = 1; i < p_VM_Info[dev_idx].DirItemAll; i++, dptr++){
        if ((sec >= p_VM_Info[dev_idx].DirList[i].First)
            &&(sec <= p_VM_Info[dev_idx].DirList[i].Last))
        {
            dptr = p_VM_Info[dev_idx].DirList[i];
            break;
        }
    }

    p_VM_Info[dev_idx].NowName = "";
    do {
        p_VM_Info[dev_idx].NowName = SEPARATOR + p_VM_Info[dev_idx].DirList[i].FullName +
                                     p_VM_Info[dev_idx].NowName; //LINUX or MAC
        i = p_VM_Info[dev_idx].DirList[i].Link;
    } while(i);

    p_VM_Info[dev_idx].LastName = p_VM_Info[dev_idx].NowName;
    p_VM_Info[dev_idx].NowName = p_VM_Info[dev_idx].DirPath + p_VM_Info[dev_idx].NowName;

    var off = (sec - dptr.First) * 512;
    if (off < dptr.Size) {
        len = dptr.Size - off;
        if (len > 512) {
            len = 512;
        }
        var file = GetFileFromName(dev_idx, p_VM_Info[dev_idx].NowName);
        if (file != null) {
            var reader = new FileReaderSync();
            WorkBuf = new Uint8Array(reader.readAsArrayBuffer(file.slice(off, off + len)));
            if (len < 512) {
                var temp = WorkBuf;
                WorkBuf = new Uint8Array(512);
                WorkBuf.fill(0);
                WorkBuf.set(temp, 0);
            }
        } else {
            WorkBuf.fill(0);
        }
    } else {
        WorkBuf.fill(0);
    }

    return [WorkBuf, offset];
}

function detectOS(){
    var OSName;
    if (navigator.appVersion.indexOf("Win") != -1) {
        OSName="Windows";
    } else if (navigator.appVersion.indexOf("Mac") != -1) {
        OSName="MacOS";
    } else if (navigator.appVersion.indexOf("X11") != -1) {
        OSName="UNIX";
    } else if (navigator.appVersion.indexOf("Linux") != -1) {
        OSName ="Linux";
    } else {
        OSName = "Unknown OS";
    }
    return OSName;
}

function TFATFileSystemImage_VirtualWrite(dev_idx, rx_buf, rx_buf_index, sec)
{
    var idx,insert_pos = [0];
    var fs, fs_offset;
    var ret = 0;
    var buf= new Uint8Array(UnitLen);

    ArrayCopy(buf, 0, rxbuf, rx_buf_index, UnitLen);

    if(sec <= p_VM_Info[dev_idx].SecFileFirst)
    {
        insert_pos = 0xff;
    }
    idx = TFATFileSystemImage_getInsertPos(dev_idx, sec, insert_pos);

    if(idx == -1)
    {
        //if(Main_CheckFreeSpace(p_VM_Info[dev_idx].WriteDataTmpFileFullPath) < 2)
        //    return 0;

        idx = p_VM_Info[dev_idx].m_nTmpTotal;
        p_VM_Info[dev_idx].m_nTmpTotal++;
        TFATFileSystemImage_InsertTmpIdx(dev_idx, idx, sec, insert_pos);
    }

    p_VM_Info[dev_idx].fileSystem.root.getFile(p_VM_Info[dev_idx].f_WriteDataTmpFile, {create: false}, function(fileEntry){
        fileEntry.createWriter(function(fileWriter) {
            fileWriter.onwriteend = function(e) {
                console.log("Device " + dev_idx + ': Write completed.');
            };

            fileWriter.onerror = function(e) {
                console.log("Device " + dev_idx + ': Write failed: ' + e.toString());
            };

            if (fileWriter.length < idx * 512) {
                return;
            }

            fileWriter.seek(idx * 512);

            var blob = new Blob([buf]);
            fileWriter.write(blob);
            ret = 1;
        }, function(e) {
            console.log("Device " + dev_idx + ": fail to write tmp file," +
                            " error=" + e, dev_idx);
          });
    }, function(e) {
            console.log("Device " + dev_idx + ": fail to get tmp file," +
                            " error=" + e, dev_idx);
    });

    return ret;
}

function FileSystem_RemoveFile(dev_idx, f_name) {
    p_VM_Info[dev_idx].fileSystem.root.getFile(f_name, {}, function(fileEntry) {
        fileEntry.remove(function(){
            console.log("remove file:" + f_name);
        }, function(e) {
             console.log("Device " + dev_idx + ": fail to remove file " + f_name + ", error =" + e)
           });

    }, function(e) {
        console.log("Device " + dev_idx + ": fail to get file " + f_name + ", error =" + e)
       });
}

function Folder_RemoveImage(dev_idx)
{
/*
    TFATFileSystemImage_Wrapup(dev_idx);
*/
    var f_img_name;
    f_img_name = "vm" + dev_idx + ".ima";
    FileSystem_RemoveFile(dev_idx, f_img_name);
    FileSystem_RemoveFile(dev_idx, p_VM_Info[dev_idx].WriteDataTmpFileFullPath);
}
