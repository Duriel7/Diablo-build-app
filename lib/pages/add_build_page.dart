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

  bool _isLoading = false;
  final List<String> classes = ["Barbare", "Sorcier", "Druide", "Voleur", "Nécromancien", "Féticheur", "Croisé", "Moine"];

  void _showSuccessSnippet() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text("Le build a été forgé avec succès !"),
        backgroundColor: Colors.green,
      ),
    );
    Navigator.pop(context);
  }

  void _submit() async {
    if (_formKey.currentState!.validate()) {
      _formKey.currentState!.save();
      
      setState(() => _isLoading = true);

      try {
        final buildData = {
          'name': name,
          'characterClass': characterClass,
          'description': description,
          'game': 'Diablo IV',
          'isDraft': false,
          'imageRepository': 'builds/v1',
          'imageFileName': 'default.png',
        };

        final apiService = ApiService();
        bool success = await apiService.createBuild(buildData);

        if (success) {
          _showSuccessSnippet();
        } else {
          throw Exception("La forge a échoué. Vérifie ta connexion.");
        }
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text("Erreur : $e"), backgroundColor: Colors.red),
        );
      } finally {
        if (mounted) setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Forger un nouveau Build")),
      body: _isLoading 
        ? const Center(child: CircularProgressIndicator())
        : SingleChildScrollView(
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
                  DropdownButtonFormField<String>(
                    value: characterClass,
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
                    onPressed: _isLoading ? null : _submit,
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