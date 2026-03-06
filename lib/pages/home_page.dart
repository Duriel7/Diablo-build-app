import 'package:diablo_build_app/pages/details_page.dart';
import 'package:diablo_build_app/pages/profile_page.dart';
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
  final UserService _userService = UserService();
  final ScrollController _scrollController = ScrollController();
  
  List<Build> buildsList = [];
  bool _isLoading = true;
  bool _isFetchingMore = false;
  int _currentPage = 1;

  @override
  void initState() {
    super.initState();
    _checkStatus();
    _initLoad();

    //Scroll listener
    _scrollController.addListener(() {
      double maxScroll = _scrollController.position.maxScrollExtent;
      double currentScroll = _scrollController.position.pixels;
      
      if (currentScroll >= (maxScroll * 0.8) && !_isFetchingMore) {
        _loadMoreBuilds();
      }
    });
  }

/*   Future<void> _initLoad() async {
    final firstPage = await ApiService().fetchBuilds(page: 1);
    if (mounted) {
      setState(() {
        buildsList = firstPage;
        _isLoading = false;
      });
    }
  } */
 Future<void> _initLoad() async {
  try {
    print("Tentative de chargement des builds...");
    final firstPage = await ApiService().fetchBuilds(page: 1);
    
    print("Nombre de builds récupérés : ${firstPage.length}");

    if (mounted) {
      setState(() {
        buildsList = firstPage;
        _isLoading = false;
      });
    }
  } catch (e) {
    print("ERREUR INITIAL LOAD : $e");
    if (mounted) {
      setState(() {
        _isLoading = false; 
      });
    }
  }
}

  Future<void> _loadMoreBuilds() async {
    if (_isFetchingMore) return;
    setState(() => _isFetchingMore = true);

    _currentPage++;
    final nextBuilds = await ApiService().fetchBuilds(page: _currentPage);
    
    if (mounted) {
      setState(() {
        buildsList.addAll(nextBuilds);
        _isFetchingMore = false;
      });
    }
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
  void dispose() {
    _scrollController.dispose();
    super.dispose();
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
                    return const Center(child: CircularProgressIndicator(color: Color(0xFFC5A059)));
                  }
                  if (snapshot.hasData && snapshot.data != null) {
                    final user = snapshot.data!;
                    return InkWell(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (context) => ProfilePage(user: user)),
                        );
                      },
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.shield, color: Color(0xFFC5A059), size: 40),
                          const SizedBox(height: 10),
                          Text(
                            user['nickname']?.toUpperCase() ?? "NEPHALEM", 
                            style: const TextStyle(color: Color(0xFFC5A059), fontSize: 18, fontWeight: FontWeight.bold)
                          ),
                          Text(
                            user['email'] ?? "", 
                            style: const TextStyle(color: Colors.grey, fontSize: 12)
                          ),
                          const SizedBox(height: 5),
                          const Text(
                            "VOIR MON PROFIL", 
                            style: TextStyle(color: Color(0xFFC5A059), fontSize: 10, decoration: TextDecoration.underline)
                          ),
                        ],
                      ),
                    );
                  }
                  return const Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.account_circle, color: Colors.grey, size: 50),
                      SizedBox(height: 10),
                      Text("MODE VISITEUR", style: TextStyle(color: Colors.grey, fontSize: 16)),
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
              leading: Icon(_currentLoggedIn ? Icons.logout : Icons.person_add, color: const Color(0xFFC5A059)),
              title: Text(_currentLoggedIn ? "QUITTER LA PARTIE" : "REJOINDRE LE COMBAT", style: const TextStyle(color: Colors.white)),
              onTap: () async {
                if (_currentLoggedIn) {
                  await _userService.logout();
                  if (!mounted) return;
                  Navigator.pushNamedAndRemoveUntil(context, '/home', (route) => false);
                } else {
                  Navigator.pushNamed(context, '/login');
                }
              },
            ),
            ListTile(
               leading: const Icon(Icons.add_box, color: Colors.white),
               title: const Text("FORGER UN BUILD", style: TextStyle(color: Colors.white)),
               onTap: () => Navigator.pushNamed(context, '/add-build'),
            ),
          ],
        ),
      ),
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text("BIENVENUE SUR LE SANCTUAIRE", style: TextStyle(color: Color(0xFFC5A059), fontSize: 22, fontWeight: FontWeight.bold)),
                  SizedBox(height: 5),
                  Text("Derniers builds forgés par la communauté", style: TextStyle(color: Colors.grey, fontSize: 14)),
                ],
              ),
            ),
            Expanded(
              child: _isLoading 
                ? const Center(child: CircularProgressIndicator(color: Color(0xFFC5A059)))
                : buildsList.isEmpty
                  ? const Center(child: Text("Aucun build trouvé dans le Sanctuaire.", style: TextStyle(color: Colors.white)))
                  : ListView.separated(
                      controller: _scrollController,
                      padding: const EdgeInsets.all(12),
                      itemCount: buildsList.length + (_isFetchingMore ? 1 : 0),
                      separatorBuilder: (context, index) => const SizedBox(height: 10),
                      itemBuilder: (context, index) {
                        if (index == buildsList.length) {
                          return const Center(child: Padding(
                            padding: EdgeInsets.all(8.0),
                            child: CircularProgressIndicator(color: Color(0xFFC5A059)),
                          ));
                        }
                        final build = buildsList[index];
                        return _buildCard(build);
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