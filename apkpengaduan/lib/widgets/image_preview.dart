import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class ImagePreviewWidget extends StatelessWidget {
  final XFile file;
  final VoidCallback onRemove;

  const ImagePreviewWidget({
    super.key,
    required this.file,
    required this.onRemove,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.topRight,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child:
              kIsWeb
                  ? Image.network(
                    file.path,
                    width: 80,
                    height: 80,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) {
                      return Container(
                        width: 80,
                        height: 80,
                        color: Colors.grey.shade300,
                        child: const Icon(Icons.image, color: Colors.grey),
                      );
                    },
                  )
                  : Image.file(
                    File(file.path),
                    width: 80,
                    height: 80,
                    fit: BoxFit.cover,
                  ),
        ),
        IconButton(
          icon: const Icon(Icons.close, color: Colors.red),
          onPressed: onRemove,
        ),
      ],
    );
  }
}
