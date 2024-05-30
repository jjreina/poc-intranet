import "./style.css";

const levelsSelector = document.querySelector("#levels");
const factsSelector = document.querySelector("#facts");
const containerAC = document.querySelector(".container-2");

const createOption = (id, text) => {
  const option = document.createElement("option");
  option.text = text;
  option.value = id;
  return option;
};

const init = async () => {
  const levelsRespone = await fetch("http://localhost:3000/levels");
  const levels = await levelsRespone.json();
  levels.forEach((level) => {
    levelsSelector.appendChild(createOption(level.id, level.value));
  });
  const factsRespone = await fetch(`http://localhost:3000/facts?level=1`);
  const facts = await factsRespone.json();
  facts.forEach((fact) => {
    factsSelector.appendChild(createOption(fact.id, fact.fact));
  });
};

levelsSelector.addEventListener("change", async (event) => {
  factsSelector.innerHTML = "";
  let levelValue = event.target.value;
  const factsRespone = await fetch(
    `http://localhost:3000/facts?level=${levelValue}`
  );
  const facts = await factsRespone.json();
  facts.forEach((fact) => {
    factsSelector.appendChild(createOption(fact.id, fact.fact));
  });
  levelValue === "2"
    ? (containerAC.style.display = "flex")
    : (containerAC.style.display = "none");
});

init();
