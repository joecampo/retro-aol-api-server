<?php

//@codingStandardsIgnoreStart

namespace App\Enums;

/**
 * These are the portions of the AT packet we use to idenfity the type of atom stream.
 */
enum AtomPacket: string
{
    case GUEST_WELCOME_WINDOW = '5a*2c20*3c2f68313e*0d';
    case INSTANT_MESSAGE = '5a*20415400*000100010604000020e10109*010a01000c090103000a0201150c090102000a020114011d00010a01010302010003030101030900000c00030700010a0100001500000a020114011d00010a01030114*0114017f011100011d00010a010002010120011d000012000d';
    case CHAT_ROOM_ENTER = '5a*20415400*000100010904130000020f130101010a04000001010b01*100b040000*020201020b0200011d00010a010002010182011d00023504140000250f130102010a04000001000114*7f4f6e6c696e65486f73743a09*2068617320656e74657265642074686520726f6f6d2e011d00000701020111000007010101180400000102011d000012000d';
    case CHAT_ROOM_LEAVE = '5a*20415400*000100010904130000020f130101010a04000001010125*000c00001500000b02010b014002100b000c00001500000b020b03*011d000012000d';
    case CHAT_ROOM_PEOPLE = '5a*20415400*000100011b0104011604130000020f13010101090413000002010c010002010102011d0000070101001403200120010904130000020115*010a0100020001020203*000100040f019f04070004060272440401*040800000200011d000c010500000000010c000500000100010111000b00000b01*020201020b0200011d00010a010002010182011d00011100011804000001020012000d';
}
