class Build {
  final int id;
  final String name;
  final String characterClass;
  final String? description;
  final int authorId;
  final String game;
  final bool isDraft;
  final int version;
  final String? imageRepository;
  final String? imageFileName;

  Build({
    required this.id,
    required this.name,
    required this.characterClass,
    this.description,
    required this.authorId,
    required this.game,
    required this.isDraft,
    required this.version,
    this.imageRepository,
    this.imageFileName,
  });

  String get imageUrl {
  if (imageFileName == null || imageRepository == null) {
    return "https://placehold.co/600x400/1a1a1a/c5a059?text=Diablo+Build";
  }
  return "http://10.176.128.195:8080/uploads/builds/$imageRepository/$imageFileName";
}

  // Building Dart object from my API JSON
  factory Build.fromJson(Map<String, dynamic> json) {
    return Build(
      id: json['id'] ?? 0,
      name: json['name'] ?? 'Sans nom',
      characterClass: json['characterClass'] ?? 'Inconnue',
      description: json['description'],
      authorId: json['authorId'] ?? 0,
      game: json['game'] ?? 'Diablo',
      isDraft: json['isDraft'] == 1 || json['isDraft'] == true,
      version: json['version'] ?? 1,
      imageRepository: json['imageRepository'],
      imageFileName: json['imageFileName'],
    );
  }
}