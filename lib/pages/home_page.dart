import 'package:diablo_build_app/pages/details_page.dart';
import 'package:flutter/material.dart';
import '../models/build_model.dart';
import '../services/api_service.dart';
import '../services/user_service.dart';

class MyHomePage extends StatefulWidget {
  final String title;
  const MyHomePage({super.key, required this.title});

  @override
  State<MyHomePage> createState() => _MyHomePageState();
}

class _MyHomePageState extends State<MyHomePage> {
  bool _currentLoggedIn = false;
  late Future<List<Build>> futureBuilds;
  final UserService _userService = UserService();

  @override
  void initState() {
    super.initState();
    _checkStatus();
    futureBuilds = ApiService().fetchBuilds();
  }

  Future<void> _checkStatus() async {
      try {
        final user = await _userService.getLocalUser();
        if (mounted) {
          setState(() {
            _currentLoggedIn = (user != null);
          });
        }
      } catch (e) {
        print("Erreur check status: $e");
      }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.title)),
      drawer: Drawer(
        backgroundColor: const Color(0xFF1A1A1A),
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            DrawerHeader(
              decoration: const BoxDecoration(color: Colors.black),
              child: FutureBuilder<Map<String, dynamic>?>(
                future: _userService.getLocalUser(),
                builder: (context, snapshot) {
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const Center(
                      child: CircularProgressIndicator(color: Color(0xFFC5A059)),
                    );
                  }
                  if (snapshot.hasData && snapshot.data != null) {
                    final user = snapshot.data!;
                    return Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.shield, color: Color(0xFFC5A059), size: 40),
                        const SizedBox(height: 10),
                        Text(
                          user['nickname']?.toUpperCase() ?? "NEPHALEM",
                          style: const TextStyle(
                            color: Color(0xFFC5A059), 
                            fontSize: 18, 
                            fontWeight: FontWeight.bold
                          ),
                        ),
                        Text(
                          user['email'] ?? "",
                          style: const TextStyle(color: Colors.grey, fontSize: 12),
                        ),
                      ],
                    );
                  }
                  return const Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.account_circle, color: Colors.grey, size: 50),
                      const SizedBox(height: 10),
                      Text(
                        "MODE VISITEUR", 
                        style: TextStyle(color: Colors.grey, fontSize: 16)
                      ),
                    ],
                  );
                },
              ),
            ),
            ListTile(
              leading: const Icon(Icons.home, color: Color(0xFFC5A059)),
              title: const Text("Accueil", style: TextStyle(color: Colors.white)),
              onTap: () => Navigator.pop(context),
            ),
            ListTile(
              leading: Icon(
                _currentLoggedIn ? Icons.logout : Icons.person_add,
                color: Theme.of(context).colorScheme.secondary
              ),
              title: Text(
                _currentLoggedIn ? "QUITTER LA PARTIE" : "REJOINDRE LE COMBAT",
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              onTap: () async {
                if (_currentLoggedIn) {
                  await _userService.logout();
                  if (!context.mounted) return;
                  Navigator.pop(context);
                  Navigator.pushNamedAndRemoveUntil(context, '/home', (route) => false);
                } else {
                  Navigator.pushNamed(context, '/login');
                }
              },
            ),
            ListTile(
              leading: const Icon(Icons.add_box, color: Colors.white),
              title: const Text("FORGER UN BUILD"),
              onTap: () {
                Navigator.pushNamed(context, '/add-build');
              },
            ),
          ],
        ),
      ),
      body: SafeArea(
        bottom: true,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text("BIENVENUE SUR LE SANCTUAIRE", 
                    style: TextStyle(color: Color(0xFFC5A059), fontSize: 22, fontWeight: FontWeight.bold)),
                  SizedBox(height: 5),
                  Text("Derniers builds forgés par la communauté", 
                    style: TextStyle(color: Colors.grey, fontSize: 14)),
                ],
              ),
            ),
            Expanded(
              child: FutureBuilder<List<Build>>(
                future: futureBuilds,
                builder: (context, snapshot) {
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator(color: Color(0xFFC5A059)));
                  } else if (snapshot.hasError) {
                    return Center(child: Text("Erreur: ${snapshot.error}", style: const TextStyle(color: Colors.red)));
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
            ),
          ],
        ),
      ),
    );
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