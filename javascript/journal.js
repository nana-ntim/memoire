// Sample data for entries
const entries = [
  {
    title: "Today was a good day",
    date: "2/13/2024",
    image:
      "https://images.unsplash.com/photo-1687872843670-b8b421c837ee?q=80&w=3540&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Feeling inspired",
    date: "2/14/2024",
    image:
      "https://images.unsplash.com/photo-1547827453-026b26f91802?q=80&w=3540&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D`",
  },
  {
    title: "New project ideas",
    date: "2/15/2024",
    image:
      "https://images.unsplash.com/photo-1586871309494-e05cd95c3cfc?q=80&w=3500&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Family reunion",
    date: "2/16/2024",
    image:
      "https://images.unsplash.com/photo-1586871309494-e05cd95c3cfc?q=80&w=3500&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Exploring new hobbies",
    date: "2/17/2024",
    image:
      "https://images.unsplash.com/photo-1616794033751-fd050e9c6be1?q=80&w=3264&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Weekend getaway plans",
    date: "2/18/2024",
    image:
      "https://images.unsplash.com/photo-1523841790171-6189ac051a5c?q=80&w=3270&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Reflecting on growth",
    date: "2/19/2024",
    image:
      "https://images.unsplash.com/photo-1575687008085-1250aea2e7dd?q=80&w=3270&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Grateful for friends",
    date: "2/20/2024",
    image:
      "https://images.unsplash.com/photo-1532498551838-b7a1cfac622e?q=80&w=3270&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Future aspirations",
    date: "2/21/2024",
    image:
      "https://images.unsplash.com/photo-1507120410856-1f35574c3b45?q=80&w=3097&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Learning something new",
    date: "2/22/2024",
    image:
      "https://images.unsplash.com/photo-1698777168576-973daea99c3d?q=80&w=3270&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Overcoming challenges",
    date: "2/23/2024",
    image:
      "https://images.unsplash.com/photo-1604591949999-cc60b7de504c?q=80&w=3270&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
  {
    title: "Celebrating small wins",
    date: "2/24/2024",
    image:
      "https://images.unsplash.com/photo-1613332331768-5041274064fa?q=80&w=3270&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  },
];

const entryGrid = document.getElementById("entryGrid");

// Function to create entry cards
function createEntryCard(entry) {
  const card = document.createElement("div");
  card.className = "entry-card";
  card.innerHTML = `
        <div class="entry-image" style="background-image: url('${entry.image}');">
            <div class="entry-overlay">
                <div class="entry-title">${entry.title}</div>
                <div class="entry-date">${entry.date}</div>
            </div>
        </div>
    `;
  return card;
}

// Populate the grid with entry cards
entries.forEach((entry) => {
  entryGrid.appendChild(createEntryCard(entry));
});

// GSAP animations
gsap.from(".entry-card", {
  duration: 0.5,
  opacity: 0,
  y: 20,
  stagger: 0.1,
  ease: "power2.out",
});

// Hover animations for entry cards
document.querySelectorAll(".entry-card").forEach((card) => {
  card.addEventListener("mouseenter", () => {
    gsap.to(card, { scale: 1.05, duration: 0.2, ease: "power2.out" });
  });
  card.addEventListener("mouseleave", () => {
    gsap.to(card, { scale: 1, duration: 0.2, ease: "power2.out" });
  });
});
