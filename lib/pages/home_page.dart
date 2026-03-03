import 'package:diablo_build_app/pages/details_page.dart';
import 'package:flutter/material.dart';
import '../models/build_model.dart';
import '../services/api_service.dart';

class MyHomePage extends StatefulWidget {
  final String title;
  const MyHomePage({super.key, required this.title});

  @override
  State<MyHomePage> createState() => _MyHomePageState();
}

class _MyHomePageState extends State<MyHomePage> {
  late Future<List<Build>> futureBuilds;

  @override
  void initState() {
    super.initState();
    futureBuilds = ApiService().fetchBuilds();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.title)),
      body: SafeArea(
        bottom: true,
        child: FutureBuilder<List<Build>>(
        future: futureBuilds,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator(color: Color(0xFFC5A059)));
          } else if (snapshot.hasError) {
            return Center(child: Text("Erreur: ${snapshot.error}", style: TextStyle(color: Colors.red)));
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const Center(child: Text("Aucun build trouvé dans le Sanctuaire."));
          }

          return ListView.separated(
            padding: const EdgeInsets.all(12),
            itemCount: snapshot.data!.length,
            separatorBuilder: (context, index) => const SizedBox(height: 10),
            itemBuilder: (context, index) {
              final build = snapshot.data![index];
              return _buildCard(build);
            },
          );
        },
      ),
    ));
  }

  Widget _buildCard(Build build) {
    return Card(
      color: const Color(0xFF242424),
      shape: RoundedRectangleBorder(
        side: const BorderSide(color: Color(0xFFC5A059), width: 1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: ListTile(
        title: Text(build.name, style: const TextStyle(color: Color(0xFFC5A059), fontWeight: FontWeight.bold)),
        subtitle: Text("${build.characterClass} - ${build.game}", style: const TextStyle(color: Colors.white70)),
        trailing: const Icon(Icons.arrow_forward_ios, color: Color(0xFFC5A059), size: 16),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => DetailsPage(diabloBuild: build)),
          );
        },
      ),
    );
  }
}