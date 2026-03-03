import 'package:flutter/material.dart';
import '../models/build_model.dart';

class DetailsPage extends StatelessWidget {
  final Build diabloBuild;

  const DetailsPage({super.key, required this.diabloBuild});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(diabloBuild.name)),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Image.network(
              diabloBuild.imageUrl,
              width: double.infinity,
              height: 250,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) => Container(
                height: 250,
                color: Colors.black26,
                child: const Icon(Icons.broken_image, color: Color(0xFFC5A059), size: 50),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(diabloBuild.characterClass, 
                        style: const TextStyle(fontSize: 22, color: Color(0xFFC5A059), fontWeight: FontWeight.bold)),
                      Chip(label: Text(diabloBuild.game), backgroundColor: const Color(0xFF8B0000)),
                    ],
                  ),
                  const Divider(color: Color(0xFFC5A059)),
                  const SizedBox(height: 10),
                  const Text("DESCRIPTION", style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white70)),
                  const SizedBox(height: 8),
                  Text(diabloBuild.description ?? "Aucune description fournie.", 
                    style: const TextStyle(fontSize: 16, height: 1.4)),
                  const SizedBox(height: 20),
                  Text("Version : ${diabloBuild.version}", style: const TextStyle(color: Colors.grey, fontStyle: FontStyle.italic)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}