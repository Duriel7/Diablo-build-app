import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AddBuildPage extends StatefulWidget {
  const AddBuildPage({super.key});

  @override
  State<AddBuildPage> createState() => _AddBuildPageState();
}

class _AddBuildPageState extends State<AddBuildPage> {
  final _formKey = GlobalKey<FormState>();
  String name = "";
  String characterClass = "Barbare";
  String description = "";
  String game = "Diablo IV";

  final List<String> classes = ["Barbare", "Sorcier", "Voleur", "Nécromancien", "Druide"];

  void _submit() async {
    if (_formKey.currentState!.validate()) {
      _formKey.currentState!.save();
      // Ici on appellera l'ApiService pour envoyer les données
      // print("Envoi du build: $name pour $characterClass");
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Forger un nouveau Build")),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(
                decoration: const InputDecoration(labelText: "Nom du Build", border: OutlineInputBorder()),
                validator: (v) => v!.isEmpty ? "Entrez un nom" : null,
                onSaved: (v) => name = v!,
              ),
              const SizedBox(height: 15),
              DropdownButtonFormField(
                initialValue: characterClass,
                decoration: const InputDecoration(labelText: "Classe", border: OutlineInputBorder()),
                items: classes.map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
                onChanged: (v) => setState(() => characterClass = v!),
              ),
              const SizedBox(height: 15),
              TextFormField(
                maxLines: 3,
                decoration: const InputDecoration(labelText: "Description", border: OutlineInputBorder()),
                onSaved: (v) => description = v!,
              ),
              const SizedBox(height: 25),
              ElevatedButton(
                onPressed: _submit,
                style: ElevatedButton.styleFrom(minimumSize: const Size(double.infinity, 50)),
                child: const Text("CRÉER LE BUILD"),
              )
            ],
          ),
        ),
      ),
    );
  }
}