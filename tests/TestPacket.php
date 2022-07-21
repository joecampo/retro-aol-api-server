<?php

//@codingStandardsIgnoreStart

namespace Tests;

enum TestPacket: string
{
    case INIT_ACK_PACKET = '5ab71100037f7f240d';
    case AB_PACKET = '5a4ea900122d1c204142477565737432000000000068690d';
    case cQ_PACKET = '5ac20800197f7fa0635100200001000107040000000403010276620002000d';
    case CHAT_ROOM_ENTER_AT = '5a3cbf009b1c1420415403e5000100010904130000020f130101010a04000001010b0106477565737435100b0400000fa0020201020b0200011d00010a01000201020082011d00023504140000250f130102010a04000001000114297f4f6e6c696e65486f73743a094775657374352068617320656e74657265642074686520726f6f6d2e011d00000701020111000007010101180400000102011d000012000d5a99ce00031c14240d';
    case CHAT_ROOM_LEFT_AT = '5ae8cb0049371e2041540039000100010904130000020f130101010a04000001010125094775657374334c3455000c00001500000b02010b014002100b000c00001500000b020b03011d000012000d';
    case cQ_AT_PACKET = '5a56bf01451f162041540021000100011b0104011604130000020f13010101090413000002010c010002010102011d00000701010014032001200109041300000201150757656c636f6d65010a01000200010202031f000100040f019f040700040602724404010757656c636f6d65040800000200011d000c010500000000010c000500000100010111000b00000b01057265616f6c100b04000003e8020201020b02000b0108506f537345347553100b0400000539020201020b02000b01035a6970100b0400009ae7020201020b02000b01084775657374365a45100b0400001dc5020201020b02000b010358616b100b0400001253020201020b02000b0106477565737439100b0400001dc8020201020b02000b0109477565737450325954100b0400001dc9020201020b0200011d00010a010002010182011d00011100011804000001020012000d';
    case CJ_AT_PACKET = '5a1cc200b0161220415400180001000109032000620f13020102010a010101000a06300964656164656e640202010201020001000a06370957656c636f6d650202010201020001001006300954686520382d626974204775790202010201020001000e06300954656368204c696e6b6564020201020102000100110630094e6f7374616c676961204e657264020201020102000100070630094e65777302020102010200011100011d0000070101000701020012000d5a3bed00031612240d';
    case Dd_AT_PACKET = '5ac84702f0101020415400110001000d040002320101001303200128010901010c010500000000000c000500000000a1011d000109013501000101102401011003010010040100100c0320001e1008015b10400105103a0320001e01000108190000101702371c100b010110270100010200010a010101142b3c68313e57656c636f6d6520746f2052652d414f4c205b414c5048415d2c204775657374504f3c2f68313e01146f3c623e446973636c61696d65723c2f623e3a20576520617265206e6f742073656c6c696e6720616363657373206f722073656c6c696e6720612070726f647563743b20627574207261746865722075736572732061726520706c656467696e6720746f20737570706f72742074686501145f2070726f6a65637420616e6420696e2065786368616e676520776527726520726577617264696e67207468656d206279206c657474696e67207468656d207472792052452d414f4c20696e206561726c7920616c706861206163636573732e01142e3c62723e3c62723e596f752068617665206c6f6767656420696e2061732061204775657374213c62723e3c62723e01142a3c623e52652d414f4c3c2f623e20697320616e20414f4cae2073657276657220656d756c61746f72202d01145320776869636820747269657320746f2070726f7669646520616e20657870657269656e636520746861742077617320617661696c61626c6520647572696e6720746865203139393073272e3c62723e3c62723e011457417320612047756573742c20796f757220657870657269656e63652077696c6c206265206c696d6974656420746f206368617420726f6f6d7320616e64206d65737361676520626f617264732c20686f77657665722c2001145d796f752077696c6c206e6f7420626520616c6c6f77656420746f20706f7374206d6573736167657320746f2074686520626f617264732e2042757420796f752063616e20667265656c79206368617420696e20726f6f6d73213c62723e011d0001110320001e0010000002000d5a5a0c00031010240d5ac63e020e1110204154001200010001090320001e010a010101144d3c62723e4b65657020696e206d696e642074686174207468652073657276657220697320696e20616e203c623e414c5048413c2f623e207374617465206f6620646576656c6f706d656e742c2001144d7768696368206d65616e73206e6f742065766572797468696e6720697320676f696e6720746f20776f726b20617320796f75206d61792065787065637420697420746f6f2e3c62723e3c62723e01144a446576656c6f706d656e74206973203c753e6f6e2d676f696e6720616e6420636f6e74696e756f75733c2f753e2c2069742074616b65732074696d6520746f2072657365617263682c200114276c6561726e2c20746573742c20616e6420696d706c656d656e7420746865206368616e67657320011439736f20706c6561736520686176652070617469656e747320616e6420656e6a6f79207768617420646f657320776f726b213c62723e3c62723e01143d53657276657220646576656c6f706d656e74206973203c753e6f6e2d676f696e6720616e6420636f6e74696e756f75732e3c2f753e3c62723e3c62723e0114553c623e52652d414f4c20697320696e206e6f2077617920616666696c6961746564207769746820416d6572696361204f6e6c696e65206f7220616e79206f6620697473207375627369646961726965732e3c2f623e011d0001110320001e0010000002000d5a9a5d00031110240d5ace010019121020415400140001000400019f04060253430408000012000d5a9aad00031210240d';
    case Dd_INVALID_AT_PACKET = '5a1e910042101020415400110001000d113254686520757365726e616d65206f722070617373776f726420796f757f656e746572656420697320696e636f7272656374210002000d';
    case SC_AT_PACKET = '5a1141006713112041540d8000010001160320127f0f130101010a010210330101011100011d00011d0000070101040f019f0407000406027544050504000005390f13020203040109313333373a36323a3100070102040109313333373a36323a30000701030408000002000d';
    case uD_AT_PACKET = '5a026f003d131120415400150001000d1a0001090320001e10180957656c636f6d652c200d1d000c0701010c46000c090101000a020114011401210c0601010111000d';
    case ji_AT_PROFILE_PACKET = '5a5eb1002c1c14204154001d000100001303201e060f1302010201350000070101001403201e06013500000701020002000d5a99ce00031c14240d5a062a00e11d14204154001e000100011603201e060f13020102010903201e06013500010a01010115045079726f011100011d00010a0102010f000111000114254d656d626572204e616d653a097079726f207f4167653a09097f5365783a09094d616c657f01141e4d61726974616c205374617475733a097f4f636375706174696f6e3a097f01140d496e746572657374733a09097f011438506572736f6e616c2051756f74653a097f7f2e95b4af60952e9595207079726f20776173206865726520323032322095952e95b4af60952e011100011d00011d0000070101000701020012000d5a599f00031d14240d';
    case ji_AT_NO_PROFILE_PACKET = '5a5aee013e1c14204154001d0001010001000b0157656c6c2064616d6e21100c0329869f103a0329869f100e04010054ef1065010110400105104e04000001f4104f018c10050101104d0101010001071050010510510105100e0401005d641001010110390103010200010001081050013210510105104e04000001ab104f0163190000190f0010000100100301001004010010250100103001001031010a0114643c48544d4c3e3c5052453e3c623e54686973207573657220646f6573206e6f742063757272656e746c79206861766520612070726f66696c652c206f722074686579203c693e646f206e6f743c2f693e206578697374732e3c2f623e3c2f5052453e3c2f01140548544d4c3e010200011100010003064f4b1002010110190106105001d01051016b020409000100011c00000200010200011100001000000201000d5a99ce00031c14240d';
    case ji_AT_PROFILE_WITH_NO_BIO = '5aa1fc002c26172041540027000100001303201e060f1302010201350000070101001403201e06013500000701020002000d5a64ee00032617240d5a164e011727172041540028000100011603201e060f13020102010903201e06013500010a0101011506536c61707455011100011d00010a0102010f000111000114684d656d626572204e616d653a094d696b65204b7f4167653a090931317f5365783a09094d616c657f4d61726974616c205374617475733a09436f6d6d6f6e2d6c61777f4f636375706174696f6e3a0953757065727669736f727f496e746572657374733a0909436f0114686d7075746572732c2050726f6772616d6d696e672c20616e64203344207072696e74696e677f506572736f6e616c2051756f74653a09546865206d6f72652049206c6561726e20507974686f6e2c20746865206d6f7265207061696e66756c6c79206f6276696f75011d000002000d5aa4bf00032717240d5a5153008028172041540029000100010903201e06010a0102011460732069742069732074686174204920646f6e2774206b6e6f7720507974686f6e2e2e2e7f7f4920726576657273652d656e67696e656572656420746869732052652d414f4c20736572766572202d20796f75206172652063757272656e746c79011d000111000002000d5aa78f00032817240d5ac68100902917204154002a000100010903201e06010a0102011470207573696e6720746f207669657720746869732028756e6c657373207669657765642066726f6d20616e79206f74686572206d656469756d29202d20746f2066696c6c2061206e6f7374616c6769632067617020696e2074686520736f756c73206f662070656f706c6520776f726c64011d000111000002000d5a67de00032917240d5a487200902a17204154002b000100010903201e06010a010201147020776964652e20546865205b52655d207468617420707265636564657320414f4c207374616e647320666f722022526574726f222c20686f776576657220616e79207472616e7369746976652076657262207468617420626567696e73207769746820227265222069732067656e6572011d000111000002000d5a672e00032a17240d5abf09006f2b17204154002c000100010903201e06010a0102011444616c6c7920617070726f70726961746520666f72207573652e204e6f74653a204e6f20544f53207761732076696f6c61746564206f6e20414f4c2070726f64756374732e011100011d00011d0000070101000701020012000d5aa77f00032b17240d';
    case MAX_GLOBAL_ID_AT_PACKET = '5a1cc200b016122041540018000100010904ffffffff13020102010a010101000a06300964656164656e640202010201020001000a06370957656c636f6d650202010201020001001006300954686520382d626974204775790202010201020001000e06300954656368204c696e6b6564020201020102000100110630094e6f7374616c676961204e657264020201020102000100070630094e65777302020102010200011100011d0000070101000701020012000d5a3bed00031612240d';
    case INSTANT_MESSAGE_RECEIVED = '5a5eee012d351c204154005c000100010604000020e101090135011603010e6d002803200112010903010e6d010a01000200010202031f000100040f019f040700040602693204010756616e696c6c650408000002000c051702496e7374616e74204d6573736167652046726f6d3a200c0502033e010a010101150756616e696c6c65011d00010a01000c090103000a0201150c090102000a020114011d00010a01010302010003030101030900000c00030700010a0100001500000a020114011100011d00010a01000c090103000a0201150c090102000a020114011d00010a01010302010003030101030900000c00030700010a0100001500000a020114011d00010a010301141056616e696c6c653a2020486f776479210114017f011100011d00010a010002010120011d000012000d';
}
