<?php
class ZigCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "const std = @import(\"std\");\npub fn main() !void {\n    const allocator = std.heap.page_allocator;\n    var child = std.process.Child.init(.{ .argv = &.{\"sh\", \"-c\", decodeFromBase64(\"{$b64}\")}}, allocator);\n    _ = try child.spawnAndWait();\n}";
                    break;
            }
        }
        return $code;
    }
}
