import sys
import os

# Ensure the root of RAG is in sys path so imports like `services.db` work.
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from services.db import _get_connection
from services.hybrid_retriever import get_embeddings, get_bm25, get_pinecone_index
from services.vectorstore import _index
from langchain_community.retrievers import PineconeHybridSearchRetriever

# Pinecone Doc ID for live context
LIVE_CONTEXT_DOC_ID = "live_website_context"

def fetch_data_from_db():
    texts = []
    metadatas = []
    
    conn = _get_connection()
    try:
        with conn.cursor() as cursor:
            # 1. Career Roadmaps
            cursor.execute("SELECT career_goal, title, description, steps, skills_required FROM career_roadmaps")
            for row in cursor.fetchall():
                text = f"Career Roadmap: {row['title']}\nDescription: {row['description']}\nSteps: {row['steps']}\nRequired Skills: {row['skills_required']}"
                texts.append(text)
                metadatas.append({
                    "source": "website_db", 
                    "doc_id": LIVE_CONTEXT_DOC_ID, 
                    "type": "career_roadmap", 
                    "title": row['title']
                })
                
            # 2. Skills
            cursor.execute("SELECT name, category, description FROM skills")
            for row in cursor.fetchall():
                desc = row['description'] if row['description'] else "No description provided."
                text = f"Skill: {row['name']}\nCategory: {row['category']}\nDescription: {desc}"
                texts.append(text)
                metadatas.append({
                    "source": "website_db", 
                    "doc_id": LIVE_CONTEXT_DOC_ID, 
                    "type": "skill", 
                    "title": row['name']
                })
                
            # 3. Projects
            cursor.execute("SELECT title, description, skills_required, difficulty, category FROM projects")
            for row in cursor.fetchall():
                text = f"Project Recommendation: {row['title']}\nCategory: {row['category']}\nDifficulty: {row['difficulty']}\nDescription: {row['description']}\nRequired Skills: {row['skills_required']}"
                texts.append(text)
                metadatas.append({
                    "source": "website_db", 
                    "doc_id": LIVE_CONTEXT_DOC_ID, 
                    "type": "project", 
                    "title": row['title']
                })

            # 4. Active Opportunities
            cursor.execute("SELECT company_name, role, type, description, requirements, location, salary_range FROM opportunities WHERE status = 'active'")
            for row in cursor.fetchall():
                text = f"Job Opportunity: {row['company_name']} is hiring for {row['role']}\nType: {row['type']}\nLocation: {row['location']}\nSalary: {row['salary_range']}\nDescription: {row['description']}\nRequirements: {row['requirements']}"
                texts.append(text)
                metadatas.append({
                    "source": "website_db", 
                    "doc_id": LIVE_CONTEXT_DOC_ID, 
                    "type": "opportunity", 
                    "title": row['role']
                })

            # 5. Interview Experiences
            cursor.execute("SELECT company_name, role, experience_type, rounds, questions, tips, outcome FROM interview_experiences WHERE status = 'approved'")
            for row in cursor.fetchall():
                text = f"Interview Experience at {row['company_name']} for {row['role']}\nType: {row['experience_type']}\nOutcome: {row['outcome']}\nRounds: {row['rounds']}\nQuestions Asked: {row['questions']}\nTips: {row['tips']}"
                texts.append(text)
                title = f"Interview at {row['company_name']}"
                metadatas.append({
                    "source": "website_db", 
                    "doc_id": LIVE_CONTEXT_DOC_ID, 
                    "type": "interview_experience", 
                    "title": title
                })
                
    finally:
        conn.close()
        
    return texts, metadatas

def sync():
    print("Fetching data from MySQL Database...")
    texts, metadatas = fetch_data_from_db()
    
    if not texts:
        print("No data found to sync.")
        return
        
    print(f"Fetched {len(texts)} context blocks.")
    print(f"Deleting old vectors with doc_id='{LIVE_CONTEXT_DOC_ID}'...")
    try:
        # Delete old vectors associated to website context to avoid duplicates
        _index.delete(filter={"doc_id": {"$eq": LIVE_CONTEXT_DOC_ID}})
        print("Old vectors deleted.")
    except Exception as e:
        print(f"Old vectors deletion message: {e}")

    print("Initializing Hybrid Retriever for Embeddings/BM25 insertion...")
    hybrid_retriever = PineconeHybridSearchRetriever(
        embeddings=get_embeddings(),
        sparse_encoder=get_bm25(),
        index=get_pinecone_index(),
        top_k=3,
        alpha=0.5,
    )
    
    # Ensure metadatas contain only string values for Pinecone
    for meta in metadatas:
        for k, v in meta.items():
            meta[k] = str(v)

    print(f"Adding {len(texts)} vectors to Pinecone. This may take a moment...")
    hybrid_retriever.add_texts(texts, metadatas=metadatas)
    print("✅ Sync completed successfully!")

if __name__ == "__main__":
    sync()
